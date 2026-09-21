<?php

namespace App\Services\Feeder;

use InvalidArgumentException;

/**
 * Penanda posisi untuk penelusuran keyset, ditandatangani tetapi tidak disandi.
 *
 * Isinya sengaja bisa dibaca: pemakai utama API ini adalah pipeline data, dan
 * bisa menempelkan kursor yang macet ke dalam log lalu membacanya langsung
 * adalah keuntungan operasional yang nyata. Tanda tangan sudah membeli semua
 * yang relevan dari sisi keamanan — ia mencegah `tie` dipalsukan menjadi empat
 * juta (yaitu offset dalam yang menyamar sebagai kursor) dan mencegah kursor
 * satu tabel dipakai ulang di tabel lain.
 */
class Cursor
{
    public const VERSION = 1;

    public const PHASE_NULL = 'null';

    public const PHASE_VALUE = 'value';

    public function __construct(
        public readonly string $table,
        public readonly string $column,
        public readonly string $phase,
        public readonly ?string $value,
        public readonly int $tie,
        public readonly string $scope,
        public readonly ?string $boundaryHash,
    ) {}

    /**
     * Kursor untuk halaman pertama: fase null bila kolomnya boleh null, karena
     * MariaDB mengurutkan null lebih dulu dan `>= :v` tidak akan pernah
     * menjangkaunya.
     */
    public static function start(SeekPlan $plan, string $scope): self
    {
        return new self(
            $plan->table,
            $plan->column,
            $plan->nullable ? self::PHASE_NULL : self::PHASE_VALUE,
            null,
            0,
            $scope,
            null,
        );
    }

    public function next(?string $value, int $tie, ?string $boundaryHash): self
    {
        return new self($this->table, $this->column, self::PHASE_VALUE, $value, $tie, $this->scope, $boundaryHash);
    }

    public function advanceNullPhase(int $tie): self
    {
        return new self($this->table, $this->column, self::PHASE_NULL, null, $tie, $this->scope, null);
    }

    public function leaveNullPhase(): self
    {
        return new self($this->table, $this->column, self::PHASE_VALUE, null, 0, $this->scope, null);
    }

    public function encode(): string
    {
        $payload = json_encode([
            'v' => self::VERSION,
            't' => $this->table,
            'c' => $this->column,
            'p' => $this->phase,
            'k' => $this->value,
            'n' => $this->tie,
            's' => $this->scope,
            'b' => $this->boundaryHash,
        ]);

        return self::base64url($payload) . '.' . self::base64url(self::sign($payload));
    }

    /**
     * @throws InvalidArgumentException bila tanda tangan, tabel, atau lingkup filter tidak cocok
     */
    public static function decode(string $token, string $table, string $scope): self
    {
        $bagian = explode('.', $token);

        if (count($bagian) !== 2) {
            throw new InvalidArgumentException('Bentuk cursor tidak dikenali.');
        }

        $payload = self::base64urlDecode($bagian[0]);
        $tandaTangan = self::base64urlDecode($bagian[1]);

        if ($payload === false || $tandaTangan === false || ! hash_equals(self::sign($payload), $tandaTangan)) {
            throw new InvalidArgumentException('Tanda tangan cursor tidak sah.');
        }

        $isi = json_decode($payload, true);

        if (! is_array($isi) || ($isi['v'] ?? null) !== self::VERSION) {
            throw new InvalidArgumentException('Versi cursor tidak didukung.');
        }

        if (($isi['t'] ?? null) !== $table) {
            throw new InvalidArgumentException('Cursor ini milik tabel lain.');
        }

        // Mengubah filter di tengah penelusuran membuat rentang yang tersisa
        // tidak lagi sebangun dengan yang sudah dilewati. Lebih baik ditolak
        // daripada diam-diam mengembalikan himpunan yang berbeda bentuk.
        if (($isi['s'] ?? null) !== $scope) {
            throw new InvalidArgumentException('Filter berubah di tengah penelusuran cursor.');
        }

        return new self(
            $table,
            (string) $isi['c'],
            (string) $isi['p'],
            $isi['k'] === null ? null : (string) $isi['k'],
            max(0, (int) $isi['n']),
            $scope,
            $isi['b'] === null ? null : (string) $isi['b'],
        );
    }

    /**
     * Sidik jari satu baris, dipakai untuk mendeteksi urutan dalam grup yang
     * bergeser di antara dua request.
     */
    public static function fingerprint(array $row): string
    {
        return sha1(json_encode(array_values($row)));
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, (string) config('app.key'), true);
    }

    private static function base64url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $encoded): string|false
    {
        return base64_decode(strtr($encoded, '-_', '+/'), true);
    }
}
