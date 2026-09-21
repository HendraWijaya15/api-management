<?php

namespace App\Services\Feeder;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Metadata skema pdunsri, dibaca sekali lalu disimpan di cache.
 *
 * Sebelum kelas ini, setiap request ke endpoint data memukul information_schema
 * dua kali — sekali untuk daftar tabel, sekali untuk daftar kolom — dan basis
 * data itu berada di server lain tanpa timeout terpasang. Isinya berubah sangat
 * jarang, jadi keduanya dilayani dari cache aplikasi (koneksi lokal) dan
 * disegarkan lewat `php artisan feeder:metadata:refresh`.
 */
class TableMetadata
{
    private const CACHE_PREFIX = 'feeder:meta:v1:';

    private const CACHE_TABLES = self::CACHE_PREFIX . '__tables__';

    /** Memo dalam satu request, supaya tabel yang sama tidak dibaca dua kali. */
    private static array $memo = [];

    /**
     * Nama setiap base table di basis data pdunsri.
     */
    public static function tables(): array
    {
        return self::$memo[self::CACHE_TABLES] ??= Cache::remember(
            self::CACHE_TABLES,
            config('feeder.metadata_ttl'),
            fn() => DB::connection('pdunsri')
                ->table('information_schema.tables')
                ->where('TABLE_SCHEMA', self::databaseName())
                ->where('TABLE_TYPE', 'BASE TABLE')
                ->orderBy('TABLE_NAME')
                ->pluck('TABLE_NAME')
                ->map(fn($table) => (string) $table)
                ->all()
        );
    }

    public static function exists(string $table): bool
    {
        return in_array($table, self::tables(), true);
    }

    /**
     * Nama kolom tabel, dalam urutan aslinya.
     */
    public static function columns(string $table): array
    {
        return array_keys(self::for($table)['columns']);
    }

    /**
     * Apakah kolom itu boleh bernilai null.
     */
    public static function isNullable(string $table, string $column): bool
    {
        return self::for($table)['columns'][$column]['nullable'] ?? true;
    }

    /**
     * Indeks BTREE tabel: nama indeks => daftar kolomnya, terurut, plus
     * kardinalitas kolom pertama dan apakah indeksnya unik.
     */
    public static function indexes(string $table): array
    {
        return self::for($table)['indexes'];
    }

    /**
     * Perkiraan jumlah baris dari information_schema. Bukan angka tepat, dan
     * memang tidak perlu tepat — ia hanya dipakai untuk memilih strategi.
     */
    public static function estimatedRows(string $table): int
    {
        return self::for($table)['rows'];
    }

    /**
     * Kolom yang menjadi kolom pertama sebuah indeks BTREE, beserta
     * kardinalitasnya. Inilah satu-satunya kolom yang boleh difilter: predikat
     * kesetaraan di atasnya selalu menjadi range scan, tidak pernah full scan.
     */
    public static function leadingIndexColumns(string $table): array
    {
        $leading = [];

        foreach (self::indexes($table) as $index) {
            $first = $index['columns'][0] ?? null;

            if ($first === null) {
                continue;
            }

            // Satu kolom bisa memimpin beberapa indeks; ambil kardinalitas
            // tertinggi yang pernah dilaporkan untuknya.
            $leading[$first] = max($leading[$first] ?? 0, $index['cardinality']);
        }

        return $leading;
    }

    /**
     * Buang metadata satu tabel, atau seluruhnya.
     */
    public static function flush(?string $table = null): void
    {
        self::$memo = [];

        if ($table !== null) {
            Cache::forget(self::CACHE_PREFIX . $table);

            return;
        }

        Cache::forget(self::CACHE_TABLES);

        foreach (self::tables() as $name) {
            Cache::forget(self::CACHE_PREFIX . $name);
        }
    }

    /**
     * Metadata satu tabel, dari cache atau dibaca ulang.
     */
    private static function for(string $table): array
    {
        $key = self::CACHE_PREFIX . $table;

        return self::$memo[$key] ??= Cache::remember(
            $key,
            config('feeder.metadata_ttl'),
            fn() => self::load($table)
        );
    }

    private static function load(string $table): array
    {
        $database = self::databaseName();

        $columns = [];

        foreach (DB::connection('pdunsri')->select(
            'SELECT COLUMN_NAME, IS_NULLABLE, DATA_TYPE
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
              ORDER BY ORDINAL_POSITION',
            [$database, $table]
        ) as $row) {
            $columns[(string) $row->COLUMN_NAME] = [
                'nullable' => $row->IS_NULLABLE === 'YES',
                'type' => (string) $row->DATA_TYPE,
            ];
        }

        // Hanya BTREE. Indeks HASH tidak bisa dipakai untuk mengurutkan maupun
        // untuk range scan, jadi ia tidak menolong apa pun di sini —
        // transkrip_mahasiswa punya unique key HASH yang justru menyesatkan.
        $indexes = [];

        foreach (DB::connection('pdunsri')->select(
            'SELECT INDEX_NAME, SEQ_IN_INDEX, COLUMN_NAME, NON_UNIQUE, CARDINALITY
               FROM information_schema.STATISTICS
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_TYPE = ?
              ORDER BY INDEX_NAME, SEQ_IN_INDEX',
            [$database, $table, 'BTREE']
        ) as $row) {
            $name = (string) $row->INDEX_NAME;

            $indexes[$name]['name'] = $name;
            $indexes[$name]['unique'] = (int) $row->NON_UNIQUE === 0;
            $indexes[$name]['primary'] = $name === 'PRIMARY';
            $indexes[$name]['columns'][] = (string) $row->COLUMN_NAME;

            if ((int) $row->SEQ_IN_INDEX === 1) {
                $indexes[$name]['cardinality'] = (int) $row->CARDINALITY;
            }
        }

        foreach ($indexes as $name => $index) {
            $indexes[$name]['cardinality'] ??= 0;
        }

        $rows = DB::connection('pdunsri')->selectOne(
            'SELECT TABLE_ROWS FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, $table]
        );

        return [
            'columns' => $columns,
            'indexes' => array_values($indexes),
            'rows' => (int) ($rows->TABLE_ROWS ?? 0),
        ];
    }

    private static function databaseName(): string
    {
        return DB::connection('pdunsri')->getDatabaseName();
    }
}
