<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Feeder\SatuDataController;
use App\Http\Controllers\Api\LppmController;

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
        //✅ GET daftar mou
        Route::get('daftar-mou', [SatuDataController::class, 'get_daftar_mou'])->name('api.referensi.daftar-mou');
        //✅ GET daftar pks
        Route::get('daftar-pks', [SatuDataController::class, 'get_daftar_pks'])->name('api.referensi.daftar-pks');
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
        // GET detail_periode_perkuliahan
        Route::get('detail-periode-perkuliahan', [SatuDataController::class, 'get_detail_periode_perkuliahan'])->name('api.referensi.get-detail-periode-perkuliahan');

    // MAHASISWA
        // GET AKM
        Route::get('aktivitas-kuliah-mahasiswa', [SatuDataController::class, 'get_aktivitas_kuliah_mahasiswa'])->name('api.mahasiswa.get-aktivitas-kuliah-mahasiswa');
        // Get Biodata mahasiswa
        Route::get('biodata-mahasiswa', [SatuDataController::class, 'get_biodata_mahasiswa'])->name('api.mahasiswa.get-biodata-mahasiswa');
        



    // DOSEN
        // GET aktivitas_mengajar_dosen
        Route::get('aktivitas-mengajar-dosen', [SatuDataController::class, 'get_aktivitas_mengajar_dosen'])->name('api.dosen.get-aktivitas-mengajar-dosen');
        // GET biodata_dosen
        Route::get('biodata-dosen', [SatuDataController::class, 'get_biodata_dosen'])->name('api.dosen.get-biodata-dosen');
        // GET anggota_penelitian
        Route::get('anggota-penelitian', [SatuDataController::class, 'get_anggota_penelitian'])->name('api.dosen.get-anggota-penelitian');
        // GET basis_evaluasi
        Route::get('basis-evaluasi', [SatuDataController::class, 'get_basis_evaluasi'])->name('api.dosen.get-basis-evaluasi');
        // GET bentuk_pendidikan
        Route::get('bentuk-pendidikan', [SatuDataController::class, 'get_bentuk_pendidikan'])->name('api.dosen.get-bentuk-pendidikan');
        // GET bidang_ilmu_paten
        Route::get('bidang-ilmu-paten', [SatuDataController::class, 'get_bidang_ilmu_paten'])->name('api.dosen.get-bidang-ilmu-paten');
        // GET bidang_ilmu_publikasi
        Route::get('bidang-ilmu-publikasi', [SatuDataController::class, 'get_bidang_ilmu_publikasi'])->name('api.dosen.get-bidang-ilmu-publikasi');
        // GET bidang_keilmuan
        Route::get('bidang-keilmuan', [SatuDataController::class, 'get_bidang_keilmuan'])->name('api.dosen.get-bidang-keilmuan');
        // GET detail_ajuan_sertifikasi_dosen
        Route::get('detail-ajuan-sertifikasi-dosen', [SatuDataController::class, 'get_detail_ajuan_sertifikasi_dosen'])->name('api.dosen.get-detail-ajuan-sertifikasi-dosen');
        // GET detail_paten
        Route::get('detail-paten', [SatuDataController::class, 'get_detail_paten'])->name('api.dosen.get-detail-paten');
        // GET detail_pendidikan_formal
        Route::get('detail-pendidikan-formal', [SatuDataController::class, 'get_detail_pendidikan_formal'])->name('api.dosen.get-detail-pendidikan-formal');
        // GET detail_penelitian
        Route::get('detail-penelitian', [SatuDataController::class, 'get_detail_penelitian'])->name('api.dosen.get-detail-penelitian');
        // GET detail_penugasan_dosen
        Route::get('detail-penugasan-dosen', [SatuDataController::class, 'get_detail_penugasan_dosen'])->name('api.dosen.get-detail-penugasan-dosen');
        // GET dokumen
        Route::get('dokumen', [SatuDataController::class, 'get_dokumen'])->name('api.dosen.get-dokumen');
        // GET dokumen_paten
        Route::get('dokumen-paten', [SatuDataController::class, 'get_detail_paten'])->name('api.dosen.get-dokumen-paten');
        // GET dokumen_pendidikan_formal
        Route::get('dokumen-pendidikan-formal', [SatuDataController::class, 'get_detail_pendidikan_formal'])->name('api.dosen.get-dokumen-pendidikan-formal');
        // GET dokumen_penelitian
        Route::get('dokumen-penelitian', [SatuDataController::class, 'get_detail_penelitian'])->name('api.dosen.get-dokumen-penelitian');
        // GET dokumen_publikasi
        Route::get('dokumen-publikasi', [SatuDataController::class, 'get_dokumen_publikasi'])->name('api.dosen.get-dokumen-publikasi');
        // GET dosen_pembimbing
        Route::get('dosen-pembimbing', [SatuDataController::class, 'get_dosen_pembimbing'])->name('api.dosen.get-dosen-pembimbing');
        // GET dosen_pengajar_kelas_kuliah
        Route::get('dosen-pengajar-kelas-kuliah', [SatuDataController::class, 'get_dosen_pengajar_kelas_kuliah'])->name('api.dosen.dosen-pengajar-kelas-kuliah');
        Route::get('sister-list-ajuan-jabatan-fungsional', [SatuDataController::class, 'get_sister_list_ajuan_jabatan_fungsional'])->name('api.referensi.sister-list-ajuan-jabatan-fungsional');
        Route::get('sister-list-ajuan-kepangkatan', [SatuDataController::class, 'get_sister_list_ajuan_kepangkatan'])->name('api.referensi.sister-list-ajuan-kepangkatan');
        Route::get('sister-list-ajuan-nilai-tes', [SatuDataController::class, 'get_sister_list_ajuan_nilai_tes'])->name('api.referensi.sister-list-ajuan-nilai-tes');
        Route::get('sister-list-ajuan-pendidikan-formal', [SatuDataController::class, 'get_sister_list_ajuan_pendidikan_formal'])->name('api.referensi.sister-list-ajuan-pendidikan-formal');
        Route::get('sister-list-ajuan-sertifikasi-dosen', [SatuDataController::class, 'get_sister_list_ajuan_sertifikasi_dosen'])->name('api.referensi.sister-list-ajuan-sertifikasi-dosen');
        Route::get('sister-list-anggota-profesi', [SatuDataController::class, 'get_sister_list_anggota_profesi'])->name('api.referensi.sister-list-anggota-profesi');
        Route::get('sister-list-bahan-ajar', [SatuDataController::class, 'get_sister_list_bahan_ajar'])->name('api.referensi.sister-list-bahan-ajar');
        Route::get('sister-list-beasiswa', [SatuDataController::class, 'get_sister_list_beasiswa'])->name('api.referensi.sister-list-beasiswa');
        Route::get('sister-list-bimbingan-mahasiswa', [SatuDataController::class, 'get_sister_list_bimbingan_mahasiswa'])->name('api.referensi.sister-list-bimbingan-mahasiswa');
        Route::get('sister-list-detasering', [SatuDataController::class, 'get_sister_list_detasering'])->name('api.referensi.sister-list-detasering');
        Route::get('sister-list-diklat', [SatuDataController::class, 'get_sister_list_diklat'])->name('api.referensi.sister-list-diklat');
        Route::get('sister-list-inpassing', [SatuDataController::class, 'get_sister_list_inpassing'])->name('api.referensi.sister-list-inpassing');
        Route::get('sister-list-jabatan-fungsional', [SatuDataController::class, 'get_sister_list_jabatan_fungsional'])->name('api.referensi.sister-list-jabatan-fungsional');
        Route::get('sister-list-jabatan-struktural', [SatuDataController::class, 'get_sister_list_jabatan_struktural'])->name('api.referensi.sister-list-jabatan-struktural');
        Route::get('sister-list-kekayaan-intelektual', [SatuDataController::class, 'get_sister_list_kekayaan_intelektual'])->name('api.referensi.sister-list-kekayaan-intelektual');
        Route::get('sister-list-kepangkatan', [SatuDataController::class, 'get_sister_list_kepangkatan'])->name('api.referensi.sister-list-kepangkatan');
        Route::get('sister-list-kesejahteraan', [SatuDataController::class, 'get_sister_list_kesejahteraan'])->name('api.referensi.sister-list-kesejahteraan');
        Route::get('sister-list-nilai-tes', [SatuDataController::class, 'get_sister_list_nilai_tes'])->name('api.referensi.sister-list-nilai-tes');
        Route::get('sister-list-orasi-ilmiah', [SatuDataController::class, 'get_sister_list_orasi_ilmiah'])->name('api.referensi.sister-list-orasi-ilmiah');
        Route::get('sister-list-pembicara', [SatuDataController::class, 'get_sister_list_pembicara'])->name('api.referensi.sister-list-pembicara');
        Route::get('sister-list-pendidikan-formal', [SatuDataController::class, 'get_sister_list_pendidikan_formal'])->name('api.referensi.sister-list-pendidikan-formal');
        Route::get('sister-list-penelitian', [SatuDataController::class, 'get_sister_list_penelitian'])->name('api.referensi.sister-list-penelitian');
        Route::get('sister-list-pengabdian', [SatuDataController::class, 'get_sister_list_pengabdian'])->name('api.referensi.sister-list-pengabdian');
        Route::get('sister-list-pengajaran', [SatuDataController::class, 'get_sister_list_pengajaran'])->name('api.referensi.sister-list-pengajaran');
        Route::get('sister-list-pengelola-jurnal', [SatuDataController::class, 'get_sister_list_pengelola_jurnal'])->name('api.referensi.sister-list-pengelola-jurnal');
        Route::get('sister-list-penghargaan', [SatuDataController::class, 'get_sister_list_penghargaan'])->name('api.referensi.sister-list-penghargaan');
        Route::get('sister-list-pengujian-mahasiswa', [SatuDataController::class, 'get_sister_list_pengujian_mahasiswa'])->name('api.referensi.sister-list-pengujian-mahasiswa');
        Route::get('sister-list-penugasan', [SatuDataController::class, 'get_sister_list_penugasan'])->name('api.referensi.sister-list-penugasan');
        Route::get('sister-list-penunjang-lain', [SatuDataController::class, 'get_sister_list_penunjang_lain'])->name('api.referensi.sister-list-penunjang-lain');
        Route::get('sister-list-publikasi', [SatuDataController::class, 'get_sister_list_publikasi'])->name('api.referensi.sister-list-publikasi');
        Route::get('sister-list-riwayat-pekerjaan', [SatuDataController::class, 'get_sister_list_riwayat_pekerjaan'])->name('api.referensi.sister-list-riwayat-pekerjaan');
        Route::get('sister-list-sertifikasi-dosen', [SatuDataController::class, 'get_sister_list_sertifikasi_dosen'])->name('api.referensi.sister-list-sertifikasi-dosen');
        Route::get('sister-list-sertifikasi-profesi', [SatuDataController::class, 'get_sister_list_sertifikasi_profesi'])->name('api.referensi.sister-list-sertifikasi-profesi');
        Route::get('sister-list-tugas-tambahan', [SatuDataController::class, 'get_sister_list_tugas_tambahan'])->name('api.referensi.sister-list-tugas-tambahan');
        Route::get('sister-list-tunjangan', [SatuDataController::class, 'get_sister_list_tunjangan'])->name('api.referensi.sister-list-tunjangan');
        Route::get('sister-list-visiting-scientist', [SatuDataController::class, 'get_sister_list_visiting_scientist'])->name('api.referensi.sister-list-visiting-scientist');


    //PERKULIAHAN
        // GET detail_kelas_kuliah
        Route::get('detail-kelas-kuliah', [SatuDataController::class, 'get_detail_kelas_kuliah'])->name('api.perkuliahan.get-detail-kelas-kuliah');
        // GET detail_kurikulum
        Route::get('detail-kurikulum', [SatuDataController::class, 'get_detail_kurikulum'])->name('api.perkuliahan.get-detail-kurikulum');
        // GET detail_mata_kuliah
        Route::get('detail-mata-kuliah', [SatuDataController::class, 'get_detail_mata_kuliah'])->name('api.perkuliahan.get-detail-mata-kuliah');
        // GET detail_nilai_perkuliahan
        Route::get('detail-nilai-perkuliahan', [SatuDataController::class, 'get_detail_nilai_perkuliahan'])->name('api.perkuliahan.get-detail-nilai-perkuliahan');


        Route::get('kebutuhan-khusus', [SatuDataController::class, 'get_kebutuhan_khusus'])->name('api.referensi.kebutuhan-khusus');
        Route::get('kelompok-bidang', [SatuDataController::class, 'get_kelompok_bidang'])->name('api.referensi.kelompok-bidang');
        Route::get('kelompok-mata-kuliah', [SatuDataController::class, 'get_kelompok_mata_kuliah'])->name('api.referensi.kelompok-mata-kuliah');
        Route::get('konversi-kampus-merdeka', [SatuDataController::class, 'get_konversi_kampus_merdeka'])->name('api.perkuliahan.konversi-kampus-merdeka');
        Route::get('krs-mahasiswa', [SatuDataController::class, 'get_krs_mahasiswa'])->name('api.perkuliahan.krs-mahasiswa');
        Route::get('lembaga-pengangkat', [SatuDataController::class, 'get_lembaga_pengangkat'])->name('api.referensi.lembaga-pengangkat');
        Route::get('level-wilayah', [SatuDataController::class, 'get_level_wilayah'])->name('api.referensi.level-wilayah');
        Route::get('list-aktivitas-mahasiswa', [SatuDataController::class, 'get_list_aktivitas_mahasiswa'])->name('api.referensi.list-aktivitas-mahasiswa');
        
        Route::get('list-anggota-aktivitas-mahasiswa', [SatuDataController::class, 'get_list_anggota_aktivitas_mahasiswa'])->name('api.referensi.list-anggota-aktivitas-mahasiswa');
        Route::get('list-bimbing-mahasiswa', [SatuDataController::class, 'get_list_bimbing_mahasiswa'])->name('api.referensi.list-bimbing-mahasiswa');
        Route::get('list-dosen', [SatuDataController::class, 'get_list_dosen'])->name('api.referensi.list-dosen');
        Route::get('list-kelas-kuliah', [SatuDataController::class, 'get_list_kelas_kuliah'])->name('api.referensi.list-kelas-kuliah');
        Route::get('list-kurikulum', [SatuDataController::class, 'get_list_kurikulum'])->name('api.referensi.list-kurikulum');
        Route::get('list-mahasiswa', [SatuDataController::class, 'get_list_mahasiswa'])->name('api.referensi.list-mahasiswa');
        Route::get('list-mahasiswa-lulus-do', [SatuDataController::class, 'get_list_mahasiswa_lulus_do'])->name('api.referensi.list-mahasiswa-lulus-do');
        Route::get('list-mahasiswa-lulus-do-bak', [SatuDataController::class, 'get_list_mahasiswa_lulus_do_bak'])->name('api.referensi.list-mahasiswa-lulus-do-bak');
        Route::get('list-nilai-perkuliahan', [SatuDataController::class, 'get_list_nilai_perkuliahan'])->name('api.referensi.list-nilai-perkuliahan');
        Route::get('list-pendidikan-formal', [SatuDataController::class, 'get_list_pendidikan_formal'])->name('api.referensi.list-pendidikan-formal');
        Route::get('list-penelitian', [SatuDataController::class, 'get_list_penelitian'])->name('api.referensi.list-penelitian');
        Route::get('list-penugasan-dosen', [SatuDataController::class, 'get_list_penugasan_dosen'])->name('api.referensi.list-penugasan-dosen');
        Route::get('list-periode-perkuliahan', [SatuDataController::class, 'get_list_periode_perkuliahan'])->name('api.referensi.list-periode-perkuliahan');
        Route::get('list-prestasi-mahasiswa', [SatuDataController::class, 'get_list_prestasi_mahasiswa'])->name('api.referensi.list-prestasi-mahasiswa');
        Route::get('list-riwayat-pendidikan-mahasiswa', [SatuDataController::class, 'get_list_riwayat_pendidikan_mahasiswa'])->name('api.referensi.list-riwayat-pendidikan-mahasiswa');
        Route::get('list-skala-nilai-prodi', [SatuDataController::class, 'get_list_skala_nilai_prodi'])->name('api.referensi.list-skala-nilai-prodi');
        Route::get('list-substansi-kuliah', [SatuDataController::class, 'get_list_substansi_kuliah'])->name('api.referensi.list-substansi-kuliah');
        Route::get('list-uji-mahasiswa', [SatuDataController::class, 'get_list_uji_mahasiswa'])->name('api.referensi.list-uji-mahasiswa');
        
        Route::get('lppm-penelitian-dosen', [SatuDataController::class, 'get_lppm_penelitian_dosen'])->name('api.referensi.lppm-penelitian-dosen');
        Route::get('lppm-penelitian-scopus-dosens', [SatuDataController::class, 'get_lppm_penelitian_scopus_dosens'])->name('api.referensi.lppm-penelitian-scopus-dosens');
        Route::get('mahasiswa-lulus-do', [SatuDataController::class, 'get_mahasiswa_lulus_do'])->name('api.referensi.mahasiswa-lulus-do');
        Route::get('mata-kuliah', [SatuDataController::class, 'get_mata_kuliah'])->name('api.referensi.mata-kuliah');
        Route::get('matkul-kurikulum', [SatuDataController::class, 'get_matkul_kurikulum'])->name('api.referensi.matkul-kurikulum');
        Route::get('media-publikasi', [SatuDataController::class, 'get_media_publikasi'])->name('api.referensi.media-publikasi');

        Route::get('negara', [SatuDataController::class, 'get_negara'])->name('api.referensi.negara');
        Route::get('nilai-perkuliahan', [SatuDataController::class, 'get_nilai_perkuliahan'])->name('api.referensi.nilai-perkuliahan');
        Route::get('nilai-transfer-pendidikan', [SatuDataController::class, 'get_nilai_transfer_pendidikan'])->name('api.referensi.nilai-transfer-pendidikan');
        Route::get('pangkat-golongan', [SatuDataController::class, 'get_pangkat_golongan'])->name('api.referensi.pangkat-golongan');
        Route::get('pekerjaan', [SatuDataController::class, 'get_pekerjaan'])->name('api.referensi.pekerjaan');
        Route::get('pembiayaan', [SatuDataController::class, 'get_pembiayaan'])->name('api.referensi.pembiayaan');
        Route::get('penghasilan', [SatuDataController::class, 'get_penghasilan'])->name('api.referensi.penghasilan');
        Route::get('penugasan-dosen', [SatuDataController::class, 'get_penugasan_dosen'])->name('api.referensi.penugasan-dosen');
        Route::get('penulis-paten', [SatuDataController::class, 'get_penulis_paten'])->name('api.referensi.penulis-paten');
        Route::get('penulis-publikasi', [SatuDataController::class, 'get_penulis_publikasi'])->name('api.referensi.penulis-publikasi');
        Route::get('perguruan-tinggi', [SatuDataController::class, 'get_perguruan_tinggi'])->name('api.referensi.perguruan-tinggi');
        Route::get('periode-lampau', [SatuDataController::class, 'get_periode_lampau'])->name('api.referensi.periode-lampau');
        
        Route::get('profil-pt', [SatuDataController::class, 'get_profil_pt'])->name('api.referensi.profil-pt');
        Route::get('program-studi', [SatuDataController::class, 'get_program_studi'])->name('api.referensi.program-studi');
        Route::get('reg-daerah', [SatuDataController::class, 'get_reg_daerah'])->name('api.referensi.reg-daerah');
        Route::get('reg-propinsi', [SatuDataController::class, 'get_reg_propinsi'])->name('api.referensi.reg-propinsi');
        Route::get('reg-sma', [SatuDataController::class, 'get_reg_sma'])->name('api.referensi.reg-sma');
        Route::get('rencana-evaluasi', [SatuDataController::class, 'get_rencana_evaluasi'])->name('api.referensi.rencana-evaluasi');
        Route::get('rencana-pembelajaran', [SatuDataController::class, 'get_rencana_pembelajaran'])->name('api.referensi.rencana-pembelajaran');
        Route::get('riwayat-fungsional-dosen', [SatuDataController::class, 'get_riwayat_fungsional_dosen'])->name('api.referensi.riwayat-fungsional-dosen');
        Route::get('riwayat-nilai-mahasiswa', [SatuDataController::class, 'get_riwayat_nilai_mahasiswa'])->name('api.referensi.riwayat-nilai-mahasiswa');
        Route::get('riwayat-pangkat-dosen', [SatuDataController::class, 'get_riwayat_pangkat_dosen'])->name('api.referensi.riwayat-pangkat-dosen');
        Route::get('riwayat-pendidikan-dosen', [SatuDataController::class, 'get_riwayat_pendidikan_dosen'])->name('api.referensi.riwayat-pendidikan-dosen');
        Route::get('riwayat-pendidikan-mahasiswa', [SatuDataController::class, 'get_riwayat_pendidikan_mahasiswa'])->name('api.referensi.riwayat-pendidikan-mahasiswa');
        Route::get('riwayat-penelitian-dosen', [SatuDataController::class, 'get_riwayat_penelitian_dosen'])->name('api.referensi.riwayat-penelitian-dosen');
        Route::get('riwayat-sertifikasi-dosen', [SatuDataController::class, 'get_riwayat_sertifikasi_dosen'])->name('api.referensi.riwayat-sertifikasi-dosen');
        Route::get('semester', [SatuDataController::class, 'get_semester'])->name('api.referensi.semester');
        Route::get('simak-dosens', [SatuDataController::class, 'get_simak_dosens'])->name('api.referensi.simak-dosens');
        Route::get('simak-kehadiran-dosen', [SatuDataController::class, 'get_simak_kehadiran_dosen'])->name('api.referensi.simak-kehadiran-dosen');
        Route::get('simak-kehadiran-mahasiswa', [SatuDataController::class, 'get_simak_kehadiran_mahasiswa'])->name('api.referensi.simak-kehadiran-mahasiswa');
        Route::get('simak-kelas', [SatuDataController::class, 'get_simak_kelas'])->name('api.referensi.simak-kelas');
        Route::get('simak-mahasiswas', [SatuDataController::class, 'get_simak_mahasiswas'])->name('api.referensi.simak-mahasiswas');
        Route::get('simak-matakuliahs', [SatuDataController::class, 'get_simak_matakuliahs'])->name('api.referensi.simak-matakuliahs');
        Route::get('simak-transkrips', [SatuDataController::class, 'get_simak_transkrips'])->name('api.referensi.simak-transkrips');
        Route::get('simak-tugas-akhir', [SatuDataController::class, 'get_simak_tugas_akhir'])->name('api.referensi.simak-tugas-akhir');
        Route::get('simak-yudisium', [SatuDataController::class, 'get_simak_yudisium'])->name('api.referensi.simak-yudisium');
        
        
        Route::get('sister-agama', [SatuDataController::class, 'get_sister_agama'])->name('api.referensi.sister-agama');
        Route::get('sister-bidang-studi', [SatuDataController::class, 'get_sister_bidang_studi'])->name('api.referensi.sister-bidang-studi');
        Route::get('sister-bidang-usaha', [SatuDataController::class, 'get_sister_bidang_usaha'])->name('api.referensi.sister-bidang-usaha');

        Route::get('sister-detail-ajuan-jabatan-fungsional', [SatuDataController::class, 'get_sister_detail_ajuan_jabatan_fungsional'])->name('api.referensi.sister-detail-ajuan-jabatan-fungsional');
        Route::get('sister-detail-ajuan-kepangkatan', [SatuDataController::class, 'get_sister_detail_ajuan_kepangkatan'])->name('api.referensi.sister-detail-ajuan-kepangkatan');
        Route::get('sister-detail-ajuan-nilai-tes', [SatuDataController::class, 'get_sister_detail_ajuan_nilai_tes'])->name('api.referensi.sister-detail-ajuan-nilai-tes');
        Route::get('sister-detail-ajuan-pendidikan-formal', [SatuDataController::class, 'get_sister_detail_ajuan_pendidikan_formal'])->name('api.referensi.sister-detail-ajuan-pendidikan-formal');
        Route::get('sister-detail-ajuan-sertifikasi-dosen', [SatuDataController::class, 'get_sister_detail_ajuan_sertifikasi_dosen'])->name('api.referensi.sister-detail-ajuan-sertifikasi-dosen');
        Route::get('sister-detail-anggota-profesi', [SatuDataController::class, 'get_sister_detail_anggota_profesi'])->name('api.referensi.sister-detail-anggota-profesi');
        Route::get('sister-detail-bahan-ajar', [SatuDataController::class, 'get_sister_detail_bahan_ajar'])->name('api.referensi.sister-detail-bahan-ajar');
        Route::get('sister-detail-beasiswa', [SatuDataController::class, 'get_sister_detail_beasiswa'])->name('api.referensi.sister-detail-beasiswa');
        Route::get('sister-detail-bimbingan-mahasiswa', [SatuDataController::class, 'get_sister_detail_bimbingan_mahasiswa'])->name('api.referensi.sister-detail-bimbingan-mahasiswa');
        Route::get('sister-detail-detasering', [SatuDataController::class, 'get_sister_detail_detasering'])->name('api.referensi.sister-detail-detasering');
        Route::get('sister-detail-diklat', [SatuDataController::class, 'get_sister_detail_diklat'])->name('api.referensi.sister-detail-diklat');
        Route::get('sister-detail-inpassing', [SatuDataController::class, 'get_sister_detail_inpassing'])->name('api.referensi.sister-detail-inpassing');
        Route::get('sister-detail-jabatan-fungsional', [SatuDataController::class, 'get_sister_detail_jabatan_fungsional'])->name('api.referensi.sister-detail-jabatan-fungsional');
        Route::get('sister-detail-jabatan-struktural', [SatuDataController::class, 'get_sister_detail_jabatan_struktural'])->name('api.referensi.sister-detail-jabatan-struktural');
        Route::get('sister-detail-kekayaan-intelektual', [SatuDataController::class, 'get_sister_detail_kekayaan_intelektual'])->name('api.referensi.sister-detail-kekayaan-intelektual');
        Route::get('sister-detail-kepangkatan', [SatuDataController::class, 'get_sister_detail_kepangkatan'])->name('api.referensi.sister-detail-kepangkatan');
        Route::get('sister-detail-kesejahteraan', [SatuDataController::class, 'get_sister_detail_kesejahteraan'])->name('api.referensi.sister-detail-kesejahteraan');
        Route::get('sister-detail-nilai-tes', [SatuDataController::class, 'get_sister_detail_nilai_tes'])->name('api.referensi.sister-detail-nilai-tes');
        Route::get('sister-detail-orasi-ilmiah', [SatuDataController::class, 'get_sister_detail_orasi_ilmiah'])->name('api.referensi.sister-detail-orasi-ilmiah');
        Route::get('sister-detail-pembicara', [SatuDataController::class, 'get_sister_detail_pembicara'])->name('api.referensi.sister-detail-pembicara');
        Route::get('sister-detail-pendidikan-formal', [SatuDataController::class, 'get_sister_detail_pendidikan_formal'])->name('api.referensi.sister-detail-pendidikan-formal');
        Route::get('sister-detail-penelitian', [SatuDataController::class, 'get_sister_detail_penelitian'])->name('api.referensi.sister-detail-penelitian');
        Route::get('sister-detail-pengabdian', [SatuDataController::class, 'get_sister_detail_pengabdian'])->name('api.referensi.sister-detail-pengabdian');
        Route::get('sister-detail-pengajaran', [SatuDataController::class, 'get_sister_detail_pengajaran'])->name('api.referensi.sister-detail-pengajaran');
        Route::get('sister-detail-pengelola-jurnal', [SatuDataController::class, 'get_sister_detail_pengelola_jurnal'])->name('api.referensi.sister-detail-pengelola-jurnal');
        Route::get('sister-detail-penghargaan', [SatuDataController::class, 'get_sister_detail_penghargaan'])->name('api.referensi.sister-detail-penghargaan');
        Route::get('sister-detail-pengujian-mahasiswa', [SatuDataController::class, 'get_sister_detail_pengujian_mahasiswa'])->name('api.referensi.sister-detail-pengujian-mahasiswa');
        Route::get('sister-detail-penugasan', [SatuDataController::class, 'get_sister_detail_penugasan'])->name('api.referensi.sister-detail-penugasan');
        Route::get('sister-detail-penunjang-lain', [SatuDataController::class, 'get_sister_detail_penunjang_lain'])->name('api.referensi.sister-detail-penunjang-lain');
        Route::get('sister-detail-publikasi', [SatuDataController::class, 'get_sister_detail_publikasi'])->name('api.referensi.sister-detail-publikasi');
        Route::get('sister-detail-riwayat-pekerjaan', [SatuDataController::class, 'get_sister_detail_riwayat_pekerjaan'])->name('api.referensi.sister-detail-riwayat-pekerjaan');
        Route::get('sister-detail-sertifikasi-dosen', [SatuDataController::class, 'get_sister_detail_sertifikasi_dosen'])->name('api.referensi.sister-detail-sertifikasi-dosen');
        Route::get('sister-detail-sertifikasi-profesi', [SatuDataController::class, 'get_sister_detail_sertifikasi_profesi'])->name('api.referensi.sister-detail-sertifikasi-profesi');
        Route::get('sister-detail-tugas-tambahan', [SatuDataController::class, 'get_sister_detail_tugas_tambahan'])->name('api.referensi.sister-detail-tugas-tambahan');
        Route::get('sister-detail-tunjangan', [SatuDataController::class, 'get_sister_detail_tunjangan'])->name('api.referensi.sister-detail-tunjangan');
        Route::get('sister-detail-visiting-scientist', [SatuDataController::class, 'get_sister_detail_visiting_scientist'])->name('api.referensi.sister-detail-visiting-scientist');
        Route::get('sister-gelar-akademik', [SatuDataController::class, 'get_sister_gelar_akademik'])->name('api.referensi.sister-gelar-akademik');
        Route::get('sister-golongan-pangkat', [SatuDataController::class, 'get_sister_golongan_pangkat'])->name('api.referensi.sister-golongan-pangkat');
        Route::get('sister-ikatan-kerja', [SatuDataController::class, 'get_sister_ikatan_kerja'])->name('api.referensi.sister-ikatan-kerja');
        Route::get('sister-jabatan-fungsional', [SatuDataController::class, 'get_sister_jabatan_fungsional'])->name('api.referensi.sister-jabatan-fungsional');
        Route::get('sister-jabatan-negara', [SatuDataController::class, 'get_sister_jabatan_negara'])->name('api.referensi.sister-jabatan-negara');
        Route::get('sister-jabatan-tugas-tambahan', [SatuDataController::class, 'get_sister_jabatan_tugas_tambahan'])->name('api.referensi.sister-jabatan-tugas-tambahan');
        Route::get('sister-jenis-beasiswa', [SatuDataController::class, 'get_sister_jenis_beasiswa'])->name('api.referensi.sister-jenis-beasiswa');
        Route::get('sister-jenis-diklat', [SatuDataController::class, 'get_sister_jenis_diklat'])->name('api.referensi.sister-jenis-diklat');
        Route::get('sister-jenis-dokumen', [SatuDataController::class, 'get_sister_jenis_dokumen'])->name('api.referensi.sister-jenis-dokumen');
        Route::get('sister-jenis-keluar', [SatuDataController::class, 'get_sister_jenis_keluar'])->name('api.referensi.sister-jenis-keluar');
        Route::get('sister-jenis-kepanitiaan', [SatuDataController::class, 'get_sister_jenis_kepanitiaan'])->name('api.referensi.sister-jenis-kepanitiaan');
        Route::get('sister-jenis-kesejahteraan', [SatuDataController::class, 'get_sister_jenis_kesejahteraan'])->name('api.referensi.sister-jenis-kesejahteraan');
        Route::get('sister-jenis-pekerjaan', [SatuDataController::class, 'get_sister_jenis_pekerjaan'])->name('api.referensi.sister-jenis-pekerjaan');
        Route::get('sister-jenis-penghargaan', [SatuDataController::class, 'get_sister_jenis_penghargaan'])->name('api.referensi.sister-jenis-penghargaan');
        Route::get('sister-jenis-publikasi', [SatuDataController::class, 'get_sister_jenis_publikasi'])->name('api.referensi.sister-jenis-publikasi');
        Route::get('sister-jenis-tes', [SatuDataController::class, 'get_sister_jenis_tes'])->name('api.referensi.sister-jenis-tes');
        Route::get('sister-jenis-tunjangan', [SatuDataController::class, 'get_sister_jenis_tunjangan'])->name('api.referensi.sister-jenis-tunjangan');
        Route::get('sister-kategori-capaian-luaran', [SatuDataController::class, 'get_sister_kategori_capaian_luaran'])->name('api.referensi.sister-kategori-capaian-luaran');
        Route::get('sister-kategori-kegiatan', [SatuDataController::class, 'get_sister_kategori_kegiatan'])->name('api.referensi.sister-kategori-kegiatan');

        Route::get('sister-media-publikasi', [SatuDataController::class, 'get_sister_media_publikasi'])->name('api.referensi.sister-media-publikasi');
        Route::get('sister-negara', [SatuDataController::class, 'get_sister_negara'])->name('api.referensi.sister-negara');
        Route::get('sister-perguruan-tinggi', [SatuDataController::class, 'get_sister_perguruan_tinggi'])->name('api.referensi.sister-perguruan-tinggi');
        Route::get('sister-sdm', [SatuDataController::class, 'get_sister_sdm'])->name('api.referensi.sister-sdm');
        Route::get('sister-semester', [SatuDataController::class, 'get_sister_semester'])->name('api.referensi.sister-semester');
        Route::get('sister-status-kepegawaian', [SatuDataController::class, 'get_sister_status_kepegawaian'])->name('api.referensi.sister-status-kepegawaian');
        Route::get('sister-sumber-gaji', [SatuDataController::class, 'get_sister_sumber_gaji'])->name('api.referensi.sister-sumber-gaji');
        Route::get('sister-tingkat-penghargaan', [SatuDataController::class, 'get_sister_tingkat_penghargaan'])->name('api.referensi.sister-tingkat-penghargaan');
        Route::get('sister-wilayah', [SatuDataController::class, 'get_sister_wilayah'])->name('api.referensi.sister-wilayah');
        Route::get('status-keaktifan-pegawai', [SatuDataController::class, 'get_status_keaktifan_pegawai'])->name('api.referensi.status-keaktifan-pegawai');
        Route::get('status-kepegawaian', [SatuDataController::class, 'get_status_kepegawaian'])->name('api.referensi.status-kepegawaian');
        Route::get('status-mahasiswa', [SatuDataController::class, 'get_status_mahasiswa'])->name('api.referensi.status-mahasiswa');
        Route::get('sumber-gaji', [SatuDataController::class, 'get_sumber_gaji'])->name('api.referensi.sumber-gaji');

        Route::get('tahun-ajaran', [SatuDataController::class, 'get_tahun_ajaran'])->name('api.referensi.tahun-ajaran');
        Route::get('tingkat-penghargaan', [SatuDataController::class, 'get_tingkat_penghargaan'])->name('api.referensi.tingkat-penghargaan');
        Route::get('tingkat-prestasi', [SatuDataController::class, 'get_tingkat_prestasi'])->name('api.referensi.tingkat-prestasi');
        Route::get('transkrip-mahasiswa', [SatuDataController::class, 'get_transkrip_mahasiswa'])->name('api.referensi.transkrip-mahasiswa');
        Route::get('unit-kerja', [SatuDataController::class, 'get_unit_kerja'])->name('api.referensi.unit-kerja');
        Route::get('wilayah', [SatuDataController::class, 'get_wilayah'])->name('api.referensi.wilayah');

        //SIM LPPM
        Route::get('/desa-binaan',[LppmController::class, 'api.desa_binaan']);
        Route::get('/penelitian-dosen',[LppmController::class, 'api.penelitian_dosen']);
        Route::get('/pengabdian-dosen',[LppmController::class, 'api.pengabdian_dosen']);
    });
});