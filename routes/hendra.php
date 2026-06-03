<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Feeder\SatuDataController;


Route::post('get-token', [AuthController::class, 'getToken']);

Route::middleware(['auth:sanctum', 'check.api.access'])->group(function () {
    Route::prefix('v1')->group(function () {
//Sister_List
        Route::get('sister-list-ajuan_jabatan_fungsional', [SatuDataController::class, 'get_sister_list_ajuan_jabatan_fungsional'])->name('api.dosen.get_sister_list_ajuan_jabatan_fungsional');
        Route::get('sister-list-ajuan_kepangkatan', [SatuDataController::class, 'get_sister_list_ajuan_kepangkatan'])->name('api.dosen.get_sister_list_ajuan_kepangkatan');
        Route::get('sister-list-ajuan_nilai_tes', [SatuDataController::class, 'get_sister_list_ajuan_nilai_tes'])->name('api.dosen.get_sister_list_ajuan_nilai_tes');
        Route::get('sister-list-ajuan_pendidikan_formal', [SatuDataController::class, 'get_sister_list_ajuan_pendidikan_formal'])->name('api.dosen.get_sister_list_ajuan_pendidikan_formal');
        Route::get('sister-list-ajuan_sertifikasi_dosen', [SatuDataController::class, 'get_sister_list_ajuan_sertifikasi_dosen'])->name('api.dosen.get_sister_list_ajuan_sertifikasi_dosen');
        Route::get('sister-list-anggota_profesi', [SatuDataController::class, 'get_sister_list_anggota_profesi'])->name('api.dosen.get_sister_list_anggota_profesi');
        Route::get('sister-list-bahan_ajar', [SatuDataController::class, 'get_sister_list_bahan_ajar'])->name('api.dosen.get_sister_list_bahan_ajar');
        Route::get('sister-list-beasiswa', [SatuDataController::class, 'get_sister_list_beasiswa'])->name('api.dosen.get_sister_list_beasiswa');
        Route::get('sister-list-bimbingan_mahasiswa', [SatuDataController::class, 'get_sister_list_bimbingan_mahasiswa'])->name('api.dosen.get_sister_list_bimbingan_mahasiswa');
        Route::get('sister-list-datasering', [SatuDataController::class, 'get_sister_list_datasering'])->name('api.dosen.get_sister_list_datasering');
        Route::get('sister-list-diklat', [SatuDataController::class, 'get_sister_list_diklat'])->name('api.dosen.get_sister_list_diklat');
        Route::get('sister-list-inpassing', [SatuDataController::class, 'get_sister_list_inpassing'])->name('api.dosen.get_sister_list_inpassing');
        Route::get('sister-list-jabatan_fungsional', [SatuDataController::class, 'get_sister_list_jabatan_fungsional'])->name('api.dosen.get_sister_list_jabatan_fungsional');
        Route::get('sister-list-jabatan_struktural', [SatuDataController::class, 'get_sister_list_jabatan_struktural'])->name('api.dosen.get_sister_list_jabatan_struktural');
        Route::get('sister-list-kekayaan_intelektual', [SatuDataController::class, 'get_sister_list_kekayaan_intelektual'])->name('api.dosen.get_sister_list_kekayaan_intelektual');
        Route::get('sister-list-kepangkatan', [SatuDataController::class, 'get_sister_list_kepangkatan'])->name('api.dosen.get_sister_list_kepangkatan');
        Route::get('sister-list-kesejahteraan', [SatuDataController::class, 'get_sister_list_kesejahteraan'])->name('api.dosen.get_sister_list_kesejahteraan');
        Route::get('sister-list-nilai_tes', [SatuDataController::class, 'get_sister_list_nilai_tes'])->name('api.dosen.get_sister_list_nilai_tes');
        Route::get('sister-list-orasi_ilmiah', [SatuDataController::class, 'get_sister_list_orasi_ilmiah'])->name('api.dosen.get_sister_list_orasi_ilmiah');
        Route::get('sister-list-pembicara', [SatuDataController::class, 'get_sister_list_pembicara'])->name('api.dosen.get_sister_list_pembicara');
        Route::get('sister-list-penelitian', [SatuDataController::class, 'get_sister_list_penelitian'])->name('api.dosen.get_sister_list_penelitian');
        Route::get('sister-list-pendidikan_formal', [SatuDataController::class, 'get_sister_list_pendidikan_formal'])->name('api.dosen.get_sister_list_pendidikan_formal');
        Route::get('sister-list-pengabdian', [SatuDataController::class, 'get_sister_list_pengabdian'])->name('api.dosen.get_sister_list_pengabdian');
        Route::get('sister-list-pengajaran', [SatuDataController::class, 'get_sister_list_pengajaran'])->name('api.dosen.get_sister_list_pengajaran');
        Route::get('sister-list-pengelola_jurnal', [SatuDataController::class, 'get_sister_list_pengelola_jurnal'])->name('api.dosen.get_sister_list_pengelola_jurnal');
        Route::get('sister-list-penghargaan', [SatuDataController::class, 'get_sister_list_penghargaan'])->name('api.dosen.get_sister_list_penghargaan');
        Route::get('sister-list-pengujian_mahasiswa', [SatuDataController::class, 'get_sister_list_pengujian_mahasiswa'])->name('api.dosen.get_sister_list_pengujian_mahasiswa');
        Route::get('sister-list-penugasan', [SatuDataController::class, 'get_sister_list_penugasan'])->name('api.dosen.get_sister_list_penugasan');
        Route::get('sister-list-penunjang_lain', [SatuDataController::class, 'get_sister_list_penunjang_lain'])->name('api.dosen.get_sister_list_penunjang_lain');
        Route::get('sister-list-publikasi', [SatuDataController::class, 'get_sister_list_publikasi'])->name('api.dosen.get_sister_list_publikasi');
        Route::get('sister-list-riwayat-pekerjaan', [SatuDataController::class, 'get_sister_list_riwayat_pekerjaan'])->name('api.dosen.get_sister_list_riwayat_pekerjaan');
        Route::get('sister-list-sertifikasi-dosen', [SatuDataController::class, 'get_sister_list_sertifikasi_dosen'])->name('api.dosen.get_sister_list_sertifikasi_dosen');
        Route::get('sister-list-sertifikasi-profesi', [SatuDataController::class, 'get_sister_list_sertifikasi_profesi'])->name('api.dosen.get_sister_list_sertifikasi_profesi');
        Route::get('sister-list-tugas-tambahan', [SatuDataController::class, 'get_sister_list_tugas_tambahan'])->name('api.dosen.get_sister_list_tugas_tambahan');
        Route::get('sister-list-tunjangan', [SatuDataController::class, 'get_sister_list_tunjangan'])->name('api.dosen.get_sister_list_tunjangan');
        Route::get('sister-list-visiting-scientist', [SatuDataController::class, 'get_sister_list_visiting_scientist'])->name('api.dosen.get_sister_list_visiting_scientist');

    });

});