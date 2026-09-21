<?php

namespace App\Http\Controllers\Api\Feeder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SatuDataController extends Controller
{
    private const SENSITIVE_TABLES = [
        'configurations',
        'oauth_access_tokens',
        'oauth_auth_codes',
        'oauth_refresh_tokens',
        'password_resets',
        'personal_access_tokens',
        'token_temps',
    ];

    private const SENSITIVE_COLUMNS_EXACT = [
        'email',
        'failed_job_ids',
        'handphone',
        'hp',
        'nik',
        'npwp',
        'options',
        'password',
        'payload',
        'remember_token',
        'rw',
        'rt',
        'secret',
        'telepon',
        'token',
        'username',
    ];

    private const SENSITIVE_COLUMNS_CONTAINS = [
        'access_token',
        'alamat',
        'api_key',
        // 'biaya_kuliah' sengaja tidak disaring: satu-satunya kolom yang cocok
        // adalah aktivitas_kuliah_mahasiswa.biaya_kuliah_smt, dan nilainya
        // dibutuhkan untuk analisis sebaran UKT.
        'ds_kel',
        'dusun',
        'email',
        'exception',
        'handphone',
        'jalan',
        'kelurahan',
        'kode_pos',
        'nama_ayah',
        'nama_ibu',
        'nama_suami_istri',
        'nama_wali',
        'nomor_induk',
        'nomor_kps',
        'password',
        'penghasilan_ayah',
        'penghasilan_ibu',
        'penghasilan_wali',
        'phone',
        'private_key',
        'refresh_token',
        'remember_token',
        'sister_password',
        'tanggal_lahir',
        'telepon',
        'tempat_lahir',
        'token',
        'username',
    ];

    // Fungsi untuk mengambil data referensi dengan parameter yang fleksibel
    private function getReferensiData(
        string $table,
        array $columns,
        array $orderBy = []
    ) {
        $limit = min(max(request()->integer('limit', 100), 1), 1000);
        $offset = max(request()->integer('offset', 0), 0);

        $query = DB::connection('pdunsri')
            ->table($table);

        // Total seluruh data
        $totalData = $query->count();

        $query->select($columns);

        foreach ($orderBy as $column) {
            $query->orderBy($column, 'asc');
        }

        $data = $query
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Hasil kosong bukan galat: tabel yang memang belum berisi, dan halaman
        // terakhir sebuah penelusuran, sama-sama sah. 404 disediakan untuk tabel
        // yang tidak ada (lihat getDatabaseTableData), bukan untuk data yang nol.
        $totalPage = $totalData > 0 ? ceil($totalData / $limit) : 0;
        $currentPage = $totalData > 0 ? floor($offset / $limit) + 1 : 0;

        return response()->json([
            'limit' => $limit,
            'offset' => $offset,
            'totalData' => $totalData,
            'returnedData' => $data->count(),
            'totalPage' => $totalPage,
            'currentPage' => $currentPage,
            'data' => $data
        ], 200);
    }

    private function getDatabaseTables(): array
    {
        $databaseName = DB::connection('pdunsri')->getDatabaseName();

        return collect(DB::connection('pdunsri')->select(
            'SELECT TABLE_NAME AS table_name FROM information_schema.tables WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ? ORDER BY TABLE_NAME',
            [$databaseName, 'BASE TABLE']
        ))
            ->pluck('table_name')
            ->map(fn($table) => (string) $table)
            ->values()
            ->all();
    }

    private function isSensitiveTable(string $table): bool
    {
        return in_array($table, self::SENSITIVE_TABLES, true);
    }

    private function isSensitiveColumn(string $column): bool
    {
        $column = strtolower($column);

        if (in_array($column, self::SENSITIVE_COLUMNS_EXACT, true)) {
            return true;
        }

        foreach (self::SENSITIVE_COLUMNS_CONTAINS as $pattern) {
            if (str_contains($column, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function getSafeTableColumns(string $table): array
    {
        if ($this->isSensitiveTable($table)) {
            return [];
        }

        return collect(Schema::connection('pdunsri')->getColumnListing($table))
            ->reject(fn($column) => $this->isSensitiveColumn($column))
            ->values()
            ->all();
    }

    private function validateTableName(string $table): ?array
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return [
                'message' => 'Nama tabel tidak valid.',
                'table' => $table,
            ];
        }

        if (!in_array($table, $this->getDatabaseTables(), true)) {
            return [
                'message' => 'Tabel tidak ditemukan.',
                'table' => $table,
            ];
        }

        return null;
    }

    private function getDatabaseTableData(string $table)
    {
        if ($error = $this->validateTableName($table)) {
            return response()->json($error, 404);
        }

        $columns = $this->getSafeTableColumns($table);

        if (empty($columns)) {
            return response()->json([
                'message' => 'Tabel tidak memiliki kolom aman untuk ditampilkan.',
                'table' => $table,
                'data' => [],
            ], 403);
        }

        return $this->getReferensiData(
            $table,
            $columns,
            [$columns[0]]
        );
    }

    public function get_tables()
    {
        $tables = collect($this->getDatabaseTables())
            ->map(fn($table) => [
                'table' => $table,
                'columns' => $this->getSafeTableColumns($table),
                'available' => !$this->isSensitiveTable($table),
            ])
            ->values();

        return response()->json([
            'totalData' => $tables->count(),
            'data' => $tables,
        ], 200);
    }

    public function get_table(Request $request, string $table)
    {
        return $this->getDatabaseTableData($table);
    }

    public function __call($method, $parameters)
    {
        if (str_starts_with($method, 'get_')) {
            return $this->getDatabaseTableData(substr($method, 4));
        }

        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }
}
