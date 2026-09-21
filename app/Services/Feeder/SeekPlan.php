<?php

namespace App\Services\Feeder;

/**
 * Kolom yang dipakai menelusuri sebuah tabel dengan keyset, dan indeks yang
 * menopangnya.
 *
 * Pilihan yang tampak wajar — "pakai kolom pertama tabel kalau ia terindeks" —
 * salah, dan terukur salah. detail_nilai_perkuliahan berkolom pertama id_prodi
 * yang memang terindeks, tetapi satu nilai id_prodi bisa mencakup 398.082
 * baris; melompati grup sebesar itu adalah offset dalam yang sama persis
 * dengan yang sedang dihapus. Kolom seek karena itu dipilih berdasarkan
 * kardinalitas, bukan urutan kolom.
 */
class SeekPlan
{
    /**
     * Sebuah nilai kolom seek rata-rata tidak boleh mencakup lebih dari sekian
     * baris. Di atas ini, melewati satu grup sudah semahal offset dalam.
     */
    private const MAX_RATA_RATA_GRUP = 1000;

    public function __construct(
        public readonly string $table,
        public readonly string $column,
        public readonly string $index,
        /** Nilai kolom unik, sehingga `>` cukup dan tidak perlu hitungan seri. */
        public readonly bool $unique,
        public readonly bool $nullable,
        /** Kolom-kolom awal indeks yang sudah dipatok oleh filter kesetaraan. */
        public readonly array $pinned,
    ) {}

    /**
     * Pilih kolom seek terbaik, atau null bila tabel itu tidak bisa ditelusuri
     * dengan kursor.
     *
     * @param  array  $safeColumns  kolom yang lolos saringan sensitif
     * @param  array  $pinnedColumns  kolom yang dipatok filter kesetaraan bernilai tunggal
     */
    public static function choose(string $table, array $safeColumns, array $pinnedColumns): ?self
    {
        $rows = max(1, TableMetadata::estimatedRows($table));
        $terbaik = null;
        $skorTerbaik = -1;

        foreach (TableMetadata::indexes($table) as $index) {
            $columns = $index['columns'];

            // Panjang awalan indeks yang seluruhnya dipatok filter. Sesudah
            // awalan itu, indeks yang sama masih terurut, jadi kolom
            // berikutnyalah yang bisa dipakai untuk seek. Inilah yang
            // menyelamatkan aktivitas_kuliah_mahasiswa: sendirian ia tidak bisa
            // dikursorkan karena id_semester hanya punya seratusan nilai, tapi
            // dengan filter[id_semester] terpatok, seek berlanjut ke kolom
            // kedua idx_akm dan seluruh kuerinya menjadi range scan murni.
            $j = 0;
            while ($j < count($columns) && in_array($columns[$j], $pinnedColumns, true)) {
                $j++;
            }

            $candidate = $columns[$j] ?? null;

            if ($candidate === null || ! in_array($candidate, $safeColumns, true)) {
                continue;
            }

            $unique = $index['unique'] && count($columns) === 1;
            $nullable = TableMetadata::isNullable($table, $candidate);
            $cardinality = max(1, $index['cardinality']);

            if ($j === 0 && ! $unique && ($rows / $cardinality) > self::MAX_RATA_RATA_GRUP) {
                continue;
            }

            $skor = $cardinality;

            if ($unique && ! $nullable) {
                // Kasus sempurna: `>` yang tegas, tanpa hitungan seri dan tanpa
                // fase null sama sekali.
                $skor += 1_000_000_000;
            }

            if ($j > 0) {
                // Awalan yang dipatok sudah menjamin range scan, apa pun
                // kardinalitas kolom seeknya.
                $skor += 500_000_000;
            }

            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $terbaik = new self(
                    $table,
                    $candidate,
                    $index['name'],
                    $unique,
                    $nullable,
                    array_slice($columns, 0, $j),
                );
            }
        }

        return $terbaik;
    }
}
