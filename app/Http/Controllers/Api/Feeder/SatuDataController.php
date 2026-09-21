<?php

namespace App\Http\Controllers\Api\Feeder;

use App\Http\Controllers\Controller;
use App\Services\Feeder\TableMetadata;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        array $orderBy = [],
        array $filters = []
    ) {
        $limit = min(max(request()->integer('limit', 100), 1), 1000);
        $offset = max(request()->integer('offset', 0), 0);

        $query = DB::connection('pdunsri')
            ->table($table);

        // Filter diterapkan sebelum menghitung, supaya totalData menggambarkan
        // hasil yang difilter dan bukan seluruh isi tabel.
        $this->applyFilters($query, $filters);

        // Total seluruh data
        $totalData = $this->countRows($table, $filters, $query);

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
            'meta' => [
                'orderBy' => $orderBy[0] ?? null,
                'filterable' => array_values($this->filterableColumns($table, $columns)),
                'appliedFilters' => collect($filters)
                    ->mapWithKeys(fn($filter) => [$filter['column'] => $filter['values']])
                    ->all(),
            ],
            'data' => $data
        ], 200);
    }

    private function getDatabaseTables(): array
    {
        return TableMetadata::tables();
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

        return collect(TableMetadata::columns($table))
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
            [$columns[0]],
            $this->resolveFilters($table, $columns)
        );
    }

    /**
     * Kolom yang boleh difilter pada satu tabel.
     *
     * Syaratnya dua, dan keduanya wajib. Kolom harus lolos saringan sensitif —
     * tanpa itu, filter pada kolom yang sengaja disensor menjadi oracle: nilai
     * yang tidak dikirim tetap bisa disimpulkan dari jumlah baris yang kembali.
     * Dan kolom harus memimpin sebuah indeks BTREE, karena hanya predikat di
     * kolom pertama indeks yang menjadi range scan; kolom tengah sebuah indeks
     * komposit tampak terindeks tapi tetap full scan.
     */
    private function filterableColumns(string $table, array $safeColumns): array
    {
        return array_values(array_intersect(
            array_keys(TableMetadata::leadingIndexColumns($table)),
            $safeColumns
        ));
    }

    /**
     * Baca dan validasi `filter[kolom]=nilai` dari request.
     *
     * Nama kolom hanya pernah datang dari daftar yang boleh difilter, tidak
     * pernah langsung dari pemanggil; nilainya selalu diikat sebagai parameter.
     */
    private function resolveFilters(string $table, array $safeColumns): array
    {
        $requested = request()->query('filter');

        if (empty($requested)) {
            return [];
        }

        if (! is_array($requested)) {
            $this->tolakFilter($table, $safeColumns, 'Parameter filter harus berbentuk filter[kolom]=nilai.');
        }

        $filterable = $this->filterableColumns($table, $safeColumns);
        $maxValues = config('feeder.max_filter_values');
        $maxLength = config('feeder.max_filter_value_length');
        $filters = [];

        foreach ($requested as $column => $value) {
            if (! in_array($column, $filterable, true)) {
                $this->tolakFilter(
                    $table,
                    $safeColumns,
                    sprintf("Filter '%s' tidak didukung pada tabel '%s'.", $column, $table)
                );
            }

            $values = is_array($value) ? $value : explode(',', (string) $value);
            $values = array_values(array_filter(array_map('trim', $values), fn($v) => $v !== ''));

            if (empty($values)) {
                $this->tolakFilter(
                    $table,
                    $safeColumns,
                    sprintf("Filter '%s' tidak boleh kosong.", $column)
                );
            }

            if (count($values) > $maxValues) {
                $this->tolakFilter(
                    $table,
                    $safeColumns,
                    sprintf("Filter '%s' melebihi %d nilai.", $column, $maxValues)
                );
            }

            foreach ($values as $v) {
                if (mb_strlen($v) > $maxLength) {
                    $this->tolakFilter(
                        $table,
                        $safeColumns,
                        sprintf("Nilai filter '%s' melebihi %d karakter.", $column, $maxLength)
                    );
                }
            }

            $filters[] = ['column' => $column, 'values' => $values];
        }

        return $filters;
    }

    /**
     * 422 yang sekaligus menjadi dokumentasi: pemanggil yang salah menebak
     * sekali langsung tahu seluruh kolom yang boleh difilter di tabel itu.
     */
    private function tolakFilter(string $table, array $safeColumns, string $message): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $message,
            'table' => $table,
            'filterableColumns' => $this->filterableColumns($table, $safeColumns),
        ], 422));
    }

    private function applyFilters($query, array $filters): void
    {
        foreach ($filters as $filter) {
            count($filter['values']) === 1
                ? $query->where($filter['column'], $filter['values'][0])
                : $query->whereIn($filter['column'], $filter['values']);
        }
    }

    /**
     * COUNT(*) dengan cache untuk tabel besar.
     *
     * Menghitung krs_mahasiswa memakan sekitar 0,8 detik, dan itu dibayar pada
     * setiap halaman — satu penelusuran penuh dengan limit 1000 berarti enam
     * ribu kali menghitung angka yang sama. Tabel di bawah ambang selalu
     * dihitung tepat dan tidak pernah di-cache.
     */
    private function countRows(string $table, array $filters, $query): int
    {
        if (TableMetadata::estimatedRows($table) < config('feeder.count_exact_max_rows')) {
            return $query->count();
        }

        $signature = collect($filters)
            ->map(fn($filter) => $filter['column'] . '=' . implode(',', $filter['values']))
            ->sort()
            ->implode('&');

        return Cache::remember(
            'feeder:count:v1:' . $table . ':' . sha1($signature),
            config('feeder.count_ttl'),
            fn() => $query->count()
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
