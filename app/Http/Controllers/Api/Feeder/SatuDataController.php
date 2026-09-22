<?php

namespace App\Http\Controllers\Api\Feeder;

use App\Http\Controllers\Controller;
use App\Services\Feeder\Cursor;
use App\Services\Feeder\SeekPlan;
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
            'nextCursor' => null,
            'meta' => [
                'mode' => 'offset',
                'orderBy' => $orderBy[0] ?? null,
                'cursorSupported' => SeekPlan::choose($table, $columns, $this->pinnedColumns($filters)) !== null,
                'filterable' => $this->filterableColumns($table, $columns),
                'appliedFilters' => collect($filters)
                    ->mapWithKeys(fn($filter) => [$filter['column'] => $filter['values']])
                    ->all(),
                'warnings' => $offset > config('feeder.deep_offset_warn')
                    ? ['Offset sedalam ini mahal; gunakan cursor untuk penelusuran penuh.']
                    : [],
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

        $filters = $this->resolveFilters($table, $columns);

        if (request()->filled('cursor') && config('feeder.cursor_enabled')) {
            return $this->getCursorData($table, $columns, $filters);
        }

        return $this->getReferensiData(
            $table,
            $columns,
            [$columns[0]],
            $filters
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

    /**
     * Penelusuran dengan cursor.
     *
     * Rencana kueri untuk `ORDER BY ... LIMIT ... OFFSET n` di basis data ini
     * adalah full scan + filesort pada setiap offset, termasuk offset nol;
     * ongkosnya tumbuh bersama offset dan melonjak begitu filesort tumpah ke
     * disk. Terukur pada krs_mahasiswa: 0,51 detik di offset 110.000, 3,39
     * detik di 112.000, dan 18,79 detik di 4.000.000. Bentuk `WHERE kolom >= ?`
     * yang sama mendalamnya memakan 0,11 detik, karena ia range scan dan tidak
     * mengurutkan apa pun.
     *
     * Kolom seek tidak unik di 130 tabel, jadi `>` saja akan membuang baris
     * yang berbagi nilai di batas halaman. Kursor karena itu membawa dua hal:
     * nilai terakhir, dan berapa baris bernilai sama yang sudah dikirim.
     */
    private function getCursorData(string $table, array $columns, array $filters)
    {
        $plan = SeekPlan::choose($table, $columns, $this->pinnedColumns($filters));

        if ($plan === null) {
            throw new HttpResponseException(response()->json([
                'message' => "Tabel '$table' tidak mendukung cursor: tidak ada kolom berindeks yang cukup selektif untuk dipakai menelusuri.",
                'table' => $table,
                'hint' => 'Gunakan limit dan offset, atau pasang filter pada kolom pertama sebuah indeks.',
            ], 422));
        }

        $limit = min(max(request()->integer('limit', 100), 1), 1000);
        $scope = $this->filterScope($filters);
        $token = (string) request()->string('cursor');

        try {
            $cursor = $token === 'start'
                ? Cursor::start($plan, $scope)
                : Cursor::decode($token, $table, $scope);
        } catch (\InvalidArgumentException $e) {
            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
                'table' => $table,
            ], 422));
        }

        if ($cursor->tie > config('feeder.max_tie_offset')) {
            throw new HttpResponseException(response()->json([
                'message' => 'Cursor tidak dapat dilanjutkan: terlalu banyak baris berbagi satu nilai kunci.',
                'table' => $table,
            ], 422));
        }

        // Halaman lanjutan mengambil satu baris ekstra dari satu posisi lebih
        // awal, supaya baris terakhir yang sudah dikirim bisa dicocokkan
        // sidik jarinya. Bila ia tidak cocok, urutan di dalam grup bergeser
        // dan meneruskan penelusuran akan menggandakan atau membuang baris.
        $periksaBatas = $cursor->phase === Cursor::PHASE_VALUE
            && $cursor->value !== null
            && $cursor->tie > 0
            && $cursor->boundaryHash !== null;

        $skip = $periksaBatas ? $cursor->tie - 1 : $cursor->tie;
        $ambil = $periksaBatas ? $limit + 1 : $limit;

        $query = $this->seekQuery($table, $plan, $cursor, $filters);
        $rows = $query->select($columns)->offset($skip)->limit($ambil)->get();

        if ($periksaBatas) {
            $pertama = $rows->shift();

            if ($pertama === null || Cursor::fingerprint((array) $pertama) !== $cursor->boundaryHash) {
                throw new HttpResponseException(response()->json([
                    'message' => 'Cursor tidak dapat dilanjutkan karena data berubah.',
                    'table' => $table,
                ], 409));
            }
        }

        return response()->json([
            'limit' => $limit,
            'offset' => null,
            'totalData' => $this->countRows($table, $filters, $this->filteredQuery($table, $filters)),
            'returnedData' => $rows->count(),
            'totalPage' => null,
            'currentPage' => null,
            'nextCursor' => $this->nextCursor($cursor, $plan, $rows, $limit),
            'meta' => [
                'mode' => 'cursor',
                'phase' => $cursor->phase,
                'orderBy' => $plan->column,
                'seekIndex' => $plan->index,
                'filterable' => $this->filterableColumns($table, $columns),
                'appliedFilters' => collect($filters)
                    ->mapWithKeys(fn($filter) => [$filter['column'] => $filter['values']])
                    ->all(),
            ],
            'data' => $rows->values(),
        ], 200);
    }

    /**
     * Kueri untuk satu halaman cursor, sesuai fasenya.
     */
    private function seekQuery(string $table, SeekPlan $plan, Cursor $cursor, array $filters)
    {
        // MariaDB mengurutkan null lebih dulu, dan `>= ?` tidak pernah bernilai
        // benar untuk null. Tanpa fase tersendiri, baris bernilai null hanya
        // terjangkau pada halaman pertama, dan diam-diam hilang begitu
        // jumlahnya melebihi satu halaman.
        if ($cursor->phase === Cursor::PHASE_NULL) {
            return $this->filteredQuery($table, $filters)->whereNull($plan->column);
        }

        // Indeks dipaksa hanya bila tidak ada filter. Itulah yang menjamin
        // range scan, dan range scan yang membuat urutan di dalam satu grup
        // nilai tetap sama antar request.
        $paksaIndeks = empty($filters)
            && config('feeder.force_seek_index')
            && preg_match('/^[A-Za-z0-9_]+$/', $plan->index) === 1;

        $query = $paksaIndeks
            ? DB::connection('pdunsri')->table(DB::raw("`$table` FORCE INDEX (`{$plan->index}`)"))
            : $this->filteredQuery($table, $filters);

        if ($paksaIndeks) {
            $this->applyFilters($query, $filters);
        }

        if ($cursor->value === null) {
            $query->whereNotNull($plan->column);
        } else {
            // `>=`, bukan `>`: grup di batas halaman harus ikut terbaca lagi,
            // dan sisanya dibuang lewat offset seri.
            $query->where($plan->column, $plan->unique ? '>' : '>=', $cursor->value);
        }

        return $query->orderBy($plan->column, 'asc');
    }

    /**
     * Kursor untuk halaman berikutnya, atau null bila penelusuran selesai.
     */
    private function nextCursor(Cursor $cursor, SeekPlan $plan, $rows, int $limit): ?string
    {
        if ($rows->isEmpty()) {
            // Fase null yang habis bukan akhir penelusuran, hanya akhir fase.
            return $cursor->phase === Cursor::PHASE_NULL
                ? $cursor->leaveNullPhase()->encode()
                : null;
        }

        if ($cursor->phase === Cursor::PHASE_NULL) {
            return $rows->count() < $limit
                ? $cursor->leaveNullPhase()->encode()
                : $cursor->advanceNullPhase($cursor->tie + $rows->count())->encode();
        }

        $terakhir = (array) $rows->last();
        $nilaiTerakhir = $terakhir[$plan->column] ?? null;

        if ($plan->unique) {
            return $cursor->next((string) $nilaiTerakhir, 0, null)->encode();
        }

        $seri = $rows->filter(fn($row) => (((array) $row)[$plan->column] ?? null) === $nilaiTerakhir)->count();

        if ($nilaiTerakhir === $cursor->value) {
            $seri += $cursor->tie;
        }

        return $cursor->next((string) $nilaiTerakhir, $seri, Cursor::fingerprint($terakhir))->encode();
    }

    private function filteredQuery(string $table, array $filters)
    {
        $query = DB::connection('pdunsri')->table($table);
        $this->applyFilters($query, $filters);

        return $query;
    }

    /**
     * Kolom yang dipatok filter kesetaraan bernilai tunggal. Filter IN
     * menghasilkan banyak rentang, jadi ia tidak memancang apa pun.
     */
    private function pinnedColumns(array $filters): array
    {
        return collect($filters)
            ->filter(fn($filter) => count($filter['values']) === 1)
            ->pluck('column')
            ->all();
    }

    private function filterScope(array $filters): string
    {
        return sha1(collect($filters)
            ->map(fn($filter) => $filter['column'] . '=' . implode(',', $filter['values']))
            ->sort()
            ->implode('&'));
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
