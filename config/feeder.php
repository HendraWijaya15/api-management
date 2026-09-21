<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Umur cache metadata
    |--------------------------------------------------------------------------
    |
    | Daftar tabel, kolom dan indeks basis data pdunsri berubah sangat jarang,
    | tetapi dibaca pada setiap request. Nilai ini hanya jaring pengaman; cara
    | yang seharusnya dipakai untuk menyegarkan adalah
    | `php artisan feeder:metadata:refresh` sesudah skema berubah.
    |
    */

    'metadata_ttl' => env('FEEDER_METADATA_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Batas filter
    |--------------------------------------------------------------------------
    |
    | Jumlah nilai maksimum untuk satu filter (`filter[id_prodi]=a,b,c`) dan
    | panjang maksimum tiap nilai. Keduanya membatasi ukuran klausa IN yang
    | bisa diminta seorang pemanggil.
    |
    */

    'max_filter_values' => env('FEEDER_MAX_FILTER_VALUES', 50),
    'max_filter_value_length' => env('FEEDER_MAX_FILTER_VALUE_LENGTH', 255),

    /*
    |--------------------------------------------------------------------------
    | Cache COUNT(*)
    |--------------------------------------------------------------------------
    |
    | COUNT(*) pada tabel berjuta baris memakan waktu di bawah satu detik, tapi
    | ia dijalankan pada setiap halaman. Satu penelusuran penuh krs_mahasiswa
    | dengan limit 1000 berarti sekitar 6.000 kali menghitung angka yang sama.
    | Tabel yang lebih kecil dari ambang ini selalu dihitung tepat dan tidak
    | pernah di-cache.
    |
    */

    'count_exact_max_rows' => env('FEEDER_COUNT_EXACT_MAX_ROWS', 1000000),
    'count_ttl' => env('FEEDER_COUNT_TTL', 600),

];
