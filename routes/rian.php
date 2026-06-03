<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Feeder\SatuDataController;


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
            Route::post('prodi', [SatuDataController::class, 'prodi'])->name('api.feeder.prodi');
            // ✅ GET semua prodi
            Route::get('get-prodi', [SatuDataController::class, 'get_prodi'])
                ->name('api.feeder.get-prodi');

            // ✅ GET detail prodi by id_prodi
            Route::get('program-studi/{id_prodi}', [SatuDataController::class, 'informasi_prodi'])
                ->name('api.feeder.program-studi.detail');

        });

    //✅ REFERENSI

        //✅ GET prodi
        Route::get('prodi', [SatuDataController::class, 'get_prodi'])->name('api.referensi.bu.get-prodi');
        //✅ GET agama
        Route::get('agama', [SatuDataController::class, 'get_agama'])->name('api.referensi.get-agama');
        //✅ GET All Prodi
        Route::get('all-prodi', [SatuDataController::class, 'get_all_prodi'])->name('api.referensi.get-all-prodi');
        //✅ GET akreditasi prodi
        Route::get('akreditasi-prodi', [SatuDataController::class, 'get_akreditasi_prodi'])->name('api.referensi.get-akreditasi-prodi');
        //✅ GET basis evaluasi
        Route::get('basis-evaluasi', [SatuDataController::class, 'get_basis_evaluasi'])->name('api.referensi.get-basis-evaluasi');
        //✅ GET alat transportasi
        Route::get('alat-transportasi', [SatuDataController::class, 'get_alat_transportasi'])->name('api.referensi.get-alat-transportasi');
        //✅ GET Bentuk Pendidikan
        Route::get('bentuk-pendidikan', [SatuDataController::class, 'get_bentuk_pendidikan'])->name('api.referensi.get-bentuk-pendidikan');
        //✅ GET Bidang Studi
        Route::get('bidang-studi', [SatuDataController::class, 'get_bidang_studi'])->name('api.referensi.get-bidang-studi');
        //✅ GET Bidang Usaha
        Route::get('bidang-usaha', [SatuDataController::class, 'get_bidang_usaha'])->name('api.referensi.get-bidang-usaha');
        //✅ GET fakultas
        Route::get('fakultas', [SatuDataController::class, 'get_fakultas'])->name('api.referensi.get-fakultas');
        //✅ GET gelar akademik
        Route::get('gelar-akademik', [SatuDataController::class, 'get_gelar_akademik'])->name('api.referensi.get-gelar-akademik');
        //✅ GET golongan pangkat
        Route::get('golongan-pangkat', [SatuDataController::class, 'get_golongan_pangkat'])->name('api.referensi.get-golongan-pangkat');
        // GET ikatan_kerja
        Route::get('ikatan-kerja', [SatuDataController::class, 'get_ikatan_kerja'])->name('api.referensi.get-ikatan-kerja');
        // GET ikatan_kerja_sdm
        Route::get('ikatan-kerja-sdm', [SatuDataController::class, 'get_ikatan_kerja_sdm'])->name('api.referensi.get-ikatan-kerja-sdm');
        // GET jabatan_fungsional
        Route::get('jabatan-fungsional', [SatuDataController::class, 'get_jabatan_fungsional'])->name('api.referensi.get-jabatan-fungsional');
        // GET jabatan_negara
        Route::get('jabatan-negara', [SatuDataController::class, 'get_jabatan_negara'])->name('api.referensi.get-jabatan-negara');
        // GET jabatan_tugas_tambahan
        Route::get('jabatan-tugas-tambahan', [SatuDataController::class, 'get_jabatan_tugas_tambahan'])->name('api.referensi.get-jabatan-tugas-tambahan');
        // GET jalur_masuk
        Route::get('jalur-masuk', [SatuDataController::class, 'get_jalur_masuk'])->name('api.referensi.get-jalur-masuk');
        // GET jenis_aktivitas_mahasiswa
        Route::get('jenis-aktivitas-mahasiswa', [SatuDataController::class, 'get_jenis_aktivitas_mahasiswa'])->name('api.referensi.get-jenis-aktivitas-mahasiswa');
        // GET jenis_beasiswa
        Route::get('jenis-beasiswa', [SatuDataController::class, 'get_jenis_beasiswa'])->name('api.referensi.get-jenis-beasiswa');
        // GET jenis_daftar
        Route::get('jenis-daftar', [SatuDataController::class, 'get_jenis_daftar'])->name('api.referensi.get-jenis-daftar');
        // GET jenis_diklat
        Route::get('jenis-diklat', [SatuDataController::class, 'get_jenis_diklat'])->name('api.referensi.get-jenis-diklat');
        // GET jenis_dokumen
        Route::get('jenis-dokumen', [SatuDataController::class, 'get_jenis_dokumen'])->name('api.referensi.get-jenis-dokumen');
        // GET jenis_evaluasi
        Route::get('jenis-evaluasi', [SatuDataController::class, 'get_jenis_evaluasi'])->name('api.referensi.get-jenis-evaluasi');
        // GET jenis_keluar
        Route::get('jenis-keluar', [SatuDataController::class, 'get_jenis_keluar'])->name('api.referensi.get-jenis-keluar');
        // GET jenis_kepanitiaan
        Route::get('jenis-kepanitiaan', [SatuDataController::class, 'get_jenis_kepanitiaan'])->name('api.referensi.get-jenis-kepanitiaan');
        // GET jenis_kesejahteraan
        Route::get('jenis-kesejahteraan', [SatuDataController::class, 'get_jenis_kesejahteraan'])->name('api.referensi.get-jenis-kesejahteraan');
        // GET jenis_mata_kuliah
        Route::get('jenis-mata-kuliah', [SatuDataController::class, 'get_jenis_mata_kuliah'])->name('api.referensi.get-jenis-mata-kuliah');
        // GET jenis_pekerjaan
        Route::get('jenis-pekerjaan', [SatuDataController::class, 'get_jenis_pekerjaan'])->name('api.referensi.get-jenis-pekerjaan');
        // GET jenis_penghargaan
        Route::get('jenis-penghargaan', [SatuDataController::class, 'get_jenis_penghargaan'])->name('api.referensi.get-jenis-penghargaan');
        // GET jenis_prestasi
        Route::get('jenis-prestasi', [SatuDataController::class, 'get_jenis_prestasi'])->name('api.referensi.get-jenis-prestasi');
        // GET jenis_publikasi
        Route::get('jenis-publikasi', [SatuDataController::class, 'get_jenis_publikasi'])->name('api.referensi.get-jenis-publikasi');
        // GET jenis_sertifikasi
        Route::get('jenis-sertifikasi', [SatuDataController::class, 'get_jenis_sertifikasi'])->name('api.referensi.get-jenis-sertifikasi');
        // GET jenis_sms
        Route::get('jenis-sms', [SatuDataController::class, 'get_jenis_sms'])->name('api.referensi.get-jenis-sms');
        // GET jenis_substansi
        Route::get('jenis-substansi', [SatuDataController::class, 'get_jenis_substansi'])->name('api.referensi.get-jenis-substansi');
        // GET jenis_tes
        Route::get('jenis-tes', [SatuDataController::class, 'get_jenis_tes'])->name('api.referensi.get-jenis-tes');
        // GET jenis_tinggal
        Route::get('jenis-tinggal', [SatuDataController::class, 'get_jenis_tinggal'])->name('api.referensi.get-jenis-tinggal');
        // GET jenis_tunjangan
        Route::get('jenis-tunjangan', [SatuDataController::class, 'get_jenis_tunjangan'])->name('api.referensi.get-jenis-tunjangan');
        // GET jenjang_pendidikan
        Route::get('jenjang-pendidikan', [SatuDataController::class, 'get_jenjang_pendidikan'])->name('api.referensi.get-jenjang-pendidikan');
        Route::get('configurations', [SatuDataController::class, 'get_configurations'])->name('api.referensi.get-configurations');
    
    // MAHASISWA
        // GET AKM
        Route::get('aktivitas-kuliah-mahasiswa', [SatuDataController::class, 'get_aktivitas_kuliah_mahasiswa'])->name('api.mahasiswa.get_aktivitas_kuliah_mahasiswa');
        



    // DOSEN
        Route::get('aktivitas-mengajar-dosen', [SatuDataController::class, 'get_aktivitas_mengajar_dosen'])->name('api.dosen.get_aktivitas_mengajar_dosen');
        Route::get('biodata-dosen', [SatuDataController::class, 'get_biodata_dosen'])->name('api.dosen.get_biodata_dosen');

// anggota_penelitian
// basis_evaluasi
// bentuk_pendidikan
// bidang_ilmu_paten
// bidang_ilmu_publikasi
// bidang_keilmuan
// bidang_studi
// bidang_usaha
// biodata_dosen

    });


});