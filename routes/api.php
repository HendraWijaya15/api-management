<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


Route::post('get-token', [AuthController::class, 'getToken']);

Route::middleware(['auth:sanctum', 'check.api.access'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('mahasiswa-by-nim', [App\Http\Controllers\Api\MahasiswaController::class, 'index'])->name('api.mahasiswa-by-nim');
        
        // ✅ GET semua mahasiswa
        Route::get('mahasiswa-by-id-prodi', [App\Http\Controllers\Api\MahasiswaController::class, 'all_mahasiswa'])->name('api.mahasiswa-by-prodi');
        // // ✅ GET mahasiswa by id_reg
        // Route::get('mahasiswa-by-id-reg', [App\Http\Controllers\Api\MahasiswaController::class, 'mahasiswa_by_id_reg'])->name('api.mahasiswa-by-id-reg');
        // // ✅ GET AKM mahasiswa by id_reg
        Route::get('akm-by-nim', [App\Http\Controllers\Api\MahasiswaController::class, 'akm_by_nim'])->name('api.akm-by-nim');
        // ✅ GET mahasiswa Lulus DO by id_reg
        Route::get('lulus-do-by-nim', [App\Http\Controllers\Api\MahasiswaController::class, 'lulus_do_nim'])->name('api.lulus-do-by-nim');
        
        
        Route::prefix('feeder')->group(function () {
            Route::post('prodi', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'prodi'])->name('api.feeder.prodi');
            // ✅ GET semua prodi
            Route::get('get-prodi', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_prodi'])
                ->name('api.feeder.get-prodi');

            // ✅ GET detail prodi by id_prodi
            Route::get('program-studi/{id_prodi}', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'informasi_prodi'])
                ->name('api.feeder.program-studi.detail');

        });

    //✅ REFERENSI

        //✅ GET agama
        Route::get('agama', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_agama'])->name('api.referensi.get-agama');
        //✅ GET All Prodi
        Route::get('all-prodi', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_all_prodi'])->name('api.referensi.get-all-prodi');
        //✅ GET basis evaluasi
        Route::get('basis-evaluasi', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_basis_evaluasi'])->name('api.referensi.get-basis-evaluasi');
        //✅ GET alat transportasi
        Route::get('alat-transportasi', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_alat_transportasi'])->name('api.referensi.get-alat-transportasi');
        //✅ GET Bentuk Pendidikan
        Route::get('bentuk-pendidikan', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_bentuk_pendidikan'])->name('api.referensi.get-bentuk-pendidikan');
        //✅ GET Bidang Studi
        Route::get('bidang-studi', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_bidang_studi'])->name('api.referensi.get-bidang-studi');
        //✅ GET Bidang Usaha
        Route::get('bidang-usaha', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_bidang_usaha'])->name('api.referensi.get-bidang-usaha');
        //✅ GET fakultas
        Route::get('fakultas', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_fakultas'])->name('api.referensi.get-fakultas');
        //✅ GET gelar akademik
        Route::get('gelar-akademik', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_gelar_akademik'])->name('api.referensi.get-gelar-akademik');
        //✅ GET golongan pangkat
        Route::get('golongan-pangkat', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_golongan_pangkat'])->name('api.referensi.get-golongan-pangkat');
        // GET ikatan_kerja
        Route::get('ikatan-kerja', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_ikatan_kerja'])->name('api.referensi.get-ikatan-kerja');
        // GET ikatan_kerja_sdm
        Route::get('ikatan-kerja-sdm', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_ikatan_kerja_sdm'])->name('api.referensi.get-ikatan-kerja-sdm');
        // GET jabatan_fungsional
        Route::get('jabatan-fungsional', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_jabatan_fungsional'])->name('api.referensi.get-jabatan-fungsional');
        // GET jabatan_negara
        Route::get('jabatan-negara', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_jabatan_negara'])->name('api.referensi.get-jabatan-negara');
        // GET jabatan_tugas_tambahan
        Route::get('jabatan-tugas-tambahan', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_jabatan_tugas_tambahan'])->name('api.referensi.get-jabatan-tugas-tambahan');
        // GET jalur_masuk
        Route::get('jalur-masuk', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_jalur_masuk'])->name('api.referensi.get-jalur-masuk');
        // GET jenis_aktivitas_mahasiswa
        Route::get('jenis-aktivitas-mahasiswa', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_jenis_aktivitas_mahasiswa'])->name('api.referensi.get-jenis-aktivitas-mahasiswa');
        // GET jenis_beasiswa
        Route::get('jenis-beasiswa', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_jenis_beasiswa'])->name('api.referensi.get-jenis-beasiswa');
        // GET jenis_daftar
        Route::get('jenis-daftar', [App\Http\Controllers\Api\Feeder\ReferensiController::class, 'get_jenis_daftar'])->name('api.referensi.get-jenis-daftar');
    });
});



// jenis_diklat
// jenis_dokumen
// jenis_evaluasi
// jenis_keluar
// jenis_kepanitiaan
// jenis_kesejahteraan
// jenis_mata_kuliah
// jenis_pekerjaan
// jenis_penghargaan
// jenis_prestasi
// jenis_publikasi
// jenis_sertifikasi
// jenis_sms
// jenis_substansi
// jenis_tes
// jenis_tinggal
// jenis_tunjangan
// jenjang_pendidikan