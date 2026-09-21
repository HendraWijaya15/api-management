<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\ApiEndpoint;
use App\Models\User;

class GrantApiEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:grant
                            {email : Email pengguna yang diberi hak}
                            {--uri=* : Pola uri, boleh memakai * (contoh: api/v1/sister-list-*)}
                            {--method=GET : Method HTTP yang diberikan}
                            {--dry-run : Tampilkan saja, jangan simpan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memberi satu pengguna hak akses ke banyak endpoint sekaligus';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('Pengguna tidak ditemukan: ' . $this->argument('email'));

            return self::FAILURE;
        }

        $patterns = $this->option('uri');

        if (empty($patterns)) {
            $this->error('Sebutkan minimal satu --uri, contoh: --uri=api/v1/sister-list-*');

            return self::FAILURE;
        }

        $method = strtoupper($this->option('method'));

        // Pencocokan dilakukan di PHP, bukan lewat LIKE, supaya pola yang dipakai
        // di baris perintah sama persis bentuknya dengan uri yang dicocokkan
        // CheckApiAccess (yaitu `api/v1/<slug>`, tanpa garis miring di depan).
        $endpoints = ApiEndpoint::where('method', $method)
            ->get()
            ->filter(fn($endpoint) => Str::is($patterns, $endpoint->uri))
            ->values();

        if ($endpoints->isEmpty()) {
            $this->warn('Tidak ada endpoint yang cocok. Sudah menjalankan `php artisan sync:api-endpoints`?');

            return self::SUCCESS;
        }

        $sudahPunya = $user->apiEndpoints()->pluck('api_endpoints.id')->all();
        $baru = $endpoints->reject(fn($endpoint) => in_array($endpoint->id, $sudahPunya));

        foreach ($endpoints as $endpoint) {
            $tanda = in_array($endpoint->id, $sudahPunya) ? '  sudah ada' : '+ ditambahkan';
            $this->line(sprintf('%-14s %s %s', $tanda, $endpoint->method, $endpoint->uri));
        }

        if ($this->option('dry-run')) {
            $this->info(sprintf('Uji coba saja. Akan ditambahkan: %d dari %d yang cocok.', $baru->count(), $endpoints->count()));

            return self::SUCCESS;
        }

        // syncWithoutDetaching, bukan sync: hak yang sudah dipunyai pengguna ini
        // untuk endpoint lain tidak boleh ikut tercabut.
        $user->apiEndpoints()->syncWithoutDetaching($endpoints->pluck('id')->all());

        $this->info(sprintf(
            'Selesai untuk %s. Ditambahkan: %d | Sudah ada: %d',
            $user->email,
            $baru->count(),
            $endpoints->count() - $baru->count()
        ));

        return self::SUCCESS;
    }
}
