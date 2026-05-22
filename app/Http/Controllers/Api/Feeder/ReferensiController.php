<?php

namespace App\Http\Controllers\Api\Feeder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferensiController extends Controller
{
    public function prodi(Request $request)
    {
        $validated = $request->validate([
            'id_prodi' => 'nullable|string',
            'nama_jenjang_pendidikan' => 'nullable|string',
            'id_jenjang_pendidikan' => 'nullable|integer',
        ]);

        $id_prodi = isset($validated['id_prodi']) ? $validated['id_prodi'] : null;
        $jenjang = isset($validated['nama_jenjang_pendidikan']) ? $validated['nama_jenjang_pendidikan'] : null;
        $id_jenjang = isset($validated['id_jenjang_pendidikan']) ? $validated['id_jenjang_pendidikan'] : null;

        // Ambil data program studi dari database
        $prodi = DB::connection('pdunsri')->table('program_studi')
            ->when($id_prodi, function ($query) use ($id_prodi) {
                return $query->where('id_prodi', $id_prodi);
            })
            ->when($jenjang, function ($query) use ($jenjang) {
                return $query->where('nama_jenjang_pendidikan', $jenjang);
            })
            ->when($id_jenjang, function ($query) use ($id_jenjang) {
                return $query->where('id_jenjang_pendidikan', $id_jenjang);
            })
            ->select(
                'id_prodi',
                'nama_program_studi',
                'id_jenjang_pendidikan',
                'nama_jenjang_pendidikan',
                'status'
            )
            ->get();

        if ($prodi->isEmpty()) {
            return response()->json([
                'message' => 'Program studi tidak ditemukan. Periksa Kembali data yang anda masukan.',
            ], 404);
        }

        return response()->json([
            'data' => $prodi
        ]);
    }

    public function get_prodi(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $prodi = DB::connection('pdunsri')
            ->table('program_studi')
            ->select(
                'id_prodi',
                'kode_program_studi',
                'nama_jenjang_pendidikan',
                'status',
                'id_jenjang_pendidikan',
                'nama_program_studi'
            )
            ->orderBy('nama_jenjang_pendidikan', 'asc')
            ->orderBy('nama_program_studi', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($prodi->isEmpty()) {
            return response()->json([
                'message' => 'Program studi tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$prodi->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $prodi->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $prodi
        ], 200);
    }


    public function informasi_prodi($id_prodi)
    {
        $prodi = DB::connection('pdunsri')
            ->table('program_studi')
            ->where('id_prodi', $id_prodi)
            ->select(
                'id_prodi',
                'kode_program_studi',
                'id_jenjang_pendidikan',
                'nama_jenjang_pendidikan',
                'nama_program_studi',
                'status'
            )
            ->first();

        if (!$prodi) {
            return response()->json([
                'message' => 'Program studi tidak ditemukan.',
                'id_prodi' => $id_prodi
            ], 404);
        }

        return response()->json([
            'message' => 'Data program studi berhasil diambil.',
            'data' => $prodi
        ], 200);
    }

    public function get_all_prodi(Request $request)
    {
        // ✅ Ambil parameter limit & offset
        $limit  = max(100, (int) $request->limit ?? 10);
        $offset = max(0, (int) $request->offset ?? 0);

        // ✅ Batasi limit maksimal
        if ($limit > 100) {
            $limit = 100;
        }

        // ✅ Query data
        $query = DB::connection('pdunsri')
            ->table('all_prodi')
            ->select(
                'id_prodi',
                'kode_program_studi',
                'nama_jenjang_pendidikan',
                'status',
                'id_jenjang_pendidikan',
                'nama_program_studi'
            );

        // ✅ Total seluruh data tanpa limit
        $totalAllData = $query->count();

        // ✅ Ambil data dengan limit & offset
        $data = $query
            ->orderBy('nama_jenjang_pendidikan', 'asc')
            ->orderBy('nama_program_studi', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalAllData' => $totalAllData,
                'totalRow' => 0,
                'data' => []
            ], 404);
        }

        // ✅ Hitung jumlah field
        $totalRow = count((array) $data->first());

        // ✅ Response
        return response()->json([
            'limit' => $limit,
            'offset' => $offset,
            'totalData' => $data->count(),
            'totalAllData' => $totalAllData,
            'totalRow' => $totalRow,
            'data' => $data
        ], 200);
    }

    public function get_agama(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('agama')
            ->select(
                'id_agama',
                'nama_agama',
            )
            ->orderBy('id_agama', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Agama tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_basis_evaluasi(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('basis_evaluasi')
            ->select(
                'id_basis_evaluasi',
                'nama_basis_evaluasi',
            )
            ->orderBy('id_basis_evaluasi', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Basis evaluasi tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_alat_transportasi(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('alat_transportasi')
            ->select(
                'id_alat_transportasi',
                'nama_alat_transportasi',
            )
            ->orderBy('id_alat_transportasi', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_bentuk_pendidikan(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('bentuk_pendidikan')
            ->select(
                'id_bentuk_pendidikan',
                'nama_bentuk_pendidikan',
            )
            ->orderBy('id_bentuk_pendidikan', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_bidang_studi(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('bidang_studi')
            ->select(
                'id',
                'nama',
            )
            ->orderBy('nama', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_bidang_usaha(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('bidang_usaha')
            ->select(
                'id',
                'nama',
            )
            ->orderBy('nama', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_fakultas(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('fakultas')
            ->select(
                'id_fakultas',
                'nama_fakultas',
                'status',
                'id_jenjang_pendidikan',
            )
            ->orderBy('nama_fakultas', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_gelar_akademik(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('gelar_akademik')
            ->select(
                'id',
                'nama',
                'singkatan'
            )
            ->orderBy('id', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_golongan_pangkat(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('golongan_pangkat')
            ->select(
                'id',
                'nama'
            )
            ->orderBy('id', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_ikatan_kerja(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('ikatan_kerja')
            ->select(
                'id',
                'nama'
            )
            ->orderBy('id', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_ikatan_kerja_sdm(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('ikatan_kerja_sdm')
            ->select(
                'id_ikatan_kerja',
                'nama_ikatan_kerja'
            )
            ->orderBy('id_ikatan_kerja', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_jabatan_fungsional(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('jabatan_fungsional')
            ->select(
                'id_jabatan_fungsional',
                'nama_jabatan_fungsional'
            )
            ->orderBy('id_jabatan_fungsional', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_jabatan_negara(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('jabatan_negara')
            ->select(
                'id',
                'nama'
            )
            ->orderBy('id', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
    }  

    public function get_jabatan_tugas_tambahan(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('jabatan_tugas_tambahan')
            ->select(
                'id',
                'nama'
            )
            ->orderBy('id', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
     }

    public function get_jalur_masuk(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('jalur_masuk')
            ->select(
                'id_jalur_masuk',
                'nama_jalur_masuk'
            )
            ->orderBy('id_jalur_masuk', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
     }

     public function get_jenis_aktivitas_mahasiswa(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('jenis_aktivitas_mahasiswa')
            ->select(
                'id_jenis_aktivitas_mahasiswa',
                'nama_jenis_aktivitas_mahasiswa'
            )
            ->orderBy('id_jenis_aktivitas_mahasiswa', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
     }

     public function get_jenis_beasiswa(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('jenis_beasiswa')
            ->select(
                'id',
                'nama'
            )
            ->orderBy('id', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
     }

     public function get_jenis_daftar(Request $request)
    {
        // ✅ Ambil seluruh data program studi dari koneksi `pdunsri`
        $data = DB::connection('pdunsri')
            ->table('jenis_daftar')
            ->select(
                'id_jenis_daftar',
                'nama_jenis_daftar'
            )
            ->orderBy('id_jenis_daftar', 'asc')
            ->get();

        // ✅ Jika tidak ditemukan
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
                'totalData' => 0,
                'totalRow'  => 0,
                'data'      => []
            ], 404);
        }

        // ✅ Hitung jumlah kolom dari record pertama
        $totalRow = count((array)$data->first());

        // ✅ Jika ditemukan
        return response()->json([
            'totalData' => $data->count(), // jumlah baris data
            'totalRow'  => $totalRow,       // jumlah kolom (field)
            'data'      => $data
        ], 200);
     }
    
}

// 
// , 

// jenis_diklat
// id, nama

// jenis_dokumen
// id, nama

// jenis_evaluasi
// id_jenis_evaluasi, nama_jenis_evaluasi

// jenis_keluar
// id_jenis_keluar

// jenis_kepanitiaan
// id, nama

// jenis_kesejahteraan
// id, nama

// jenis_mata_kuliah
// id_jenis_mata_kuliah, nama_jenis_mata_kuliah

// jenis_pekerjaan
// id, nama

// jenis_penghargaan
// id, nama

// jenis_prestasi
// id_jenis_prestasi, nama_jenis_prestasi

// jenis_publikasi
// id, nama

// jenis_sertifikasi
// id_jenis_sertifikasi, nama_jenis_sertifikasi

// jenis_sms
// id_jenis_sms, nama_jenis_sms

// jenis_substansi
// id_jenis_substansi, nama_jenis_substansi

// jenis_tes
// id, nama

// jenis_tinggal
// id_jenis_tinggal, nama_jenis_tinggal

// jenis_tunjangan
// id, nama

// jenjang_pendidikan
// id_jenjang_didik, nama_jenjang_didik
