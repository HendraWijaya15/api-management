<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Feeder\TableMetadata;

class RefreshFeederMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeder:metadata:refresh
                            {--table= : Segarkan satu tabel saja}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Segarkan cache metadata skema pdunsri (kolom, indeks, perkiraan baris)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->option('table');

        TableMetadata::flush($table);

        if ($table !== null) {
            if (! TableMetadata::exists($table)) {
                $this->error("Tabel tidak ditemukan: $table");

                return self::FAILURE;
            }

            TableMetadata::columns($table);
            $this->info("Metadata disegarkan untuk tabel $table.");

            return self::SUCCESS;
        }

        $tables = TableMetadata::tables();

        // Panaskan ulang satu per satu supaya request pertama sesudah perintah
        // ini tidak ada yang membayar pembacaan information_schema.
        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        foreach ($tables as $name) {
            TableMetadata::columns($name);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info(sprintf('Metadata disegarkan untuk %d tabel.', count($tables)));

        return self::SUCCESS;
    }
}
