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

    private function getReferensiData(
        string $table,
        array $columns,
        string $orderBy,
        string $message = 'Data tidak ditemukan.'
    )
    {
        // dd([
        //     'table' => $table,
        //     'count' => DB::connection('pdunsri')
        //         ->table($table)
        //         ->count()
        // ]);

        $data = DB::connection('pdunsri')
            ->table($table)
            ->select($columns)
            ->orderBy($orderBy)
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'message' => $message,
                'totalData' => 0,
                'totalRow' => 0,
                'data' => []
            ], 404);
        }

        return response()->json([
            'totalData' => $data->count(),
            'totalRow' => count((array) $data->first()),
            'data' => $data
        ]);
    }

    public function get_agama()
    {
        return $this->getReferensiData(
            'agama',
            ['id_agama', 'nama_agama'],
            'id_agama',
            'Data tidak ditemukan.'
        );
    }

    public function get_basis_evaluasi(Request $request)
    {
        return $this->getReferensiData(
            'basis_evaluasi',
            ['id_basis_evaluasi', 'nama_basis_evaluasi'],
            'id_basis_evaluasi',
            'Data tidak ditemukan.'
        );
    }  

    public function get_alat_transportasi(Request $request)
    {
        return $this->getReferensiData(
            'alat_transportasi',
            ['id_alat_transportasi', 'nama_alat_transportasi'],
            'id_alat_transportasi',
            'Data tidak ditemukan.'
        );
    }  

    public function get_bentuk_pendidikan(Request $request)
    {
        return $this->getReferensiData(
            'bentuk_pendidikan',
            ['id_bentuk_pendidikan', 'nama_bentuk_pendidikan'],
            'id_bentuk_pendidikan',
            'Data tidak ditemukan.'
        );
    }  

    public function get_bidang_studi(Request $request)
    {
        return $this->getReferensiData(
            'bidang_studi',
            ['id', 'nama'],
            'nama',
            'Data tidak ditemukan.'
        );
    }

    public function get_bidang_usaha(Request $request)
    {
        return $this->getReferensiData(
            'bidang_usaha',
            ['id', 'nama'],
            'nama',
            'Data tidak ditemukan.'
        );
    }

    public function get_fakultas(Request $request)
    {
        return $this->getReferensiData(
            'fakultas',
            ['id_fakultas', 'nama_fakultas', 
                'status', 'id_jenjang_pendidikan'],
            'nama_fakultas',
            'Data tidak ditemukan.'
        );
    }

    public function get_gelar_akademik(Request $request)
    {
        return $this->getReferensiData(
            'gelar_akademik',
            ['id', 'nama', 'singkatan'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_golongan_pangkat(Request $request)
    {
        return $this->getReferensiData(
            'golongan_pangkat',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_ikatan_kerja(Request $request)
    {
        return $this->getReferensiData(
            'ikatan_kerja',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_ikatan_kerja_sdm(Request $request)
    {
        return $this->getReferensiData(
            'ikatan_kerja_sdm',
            ['id_ikatan_kerja', 'nama_ikatan_kerja'],
            'id_ikatan_kerja',
            'Data tidak ditemukan.'
        );
    }

    public function get_jabatan_fungsional(Request $request)
    {
        return $this->getReferensiData(
            'jabatan_fungsional',
            ['id_jabatan_fungsional', 'nama_jabatan_fungsional'],
            'id_jabatan_fungsional',
            'Data tidak ditemukan.'
        );
    }

    public function get_jabatan_negara(Request $request)
    {
        return $this->getReferensiData(
            'jabatan_negara',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jabatan_tugas_tambahan(Request $request)
    {
        return $this->getReferensiData(
            'jabatan_tugas_tambahan',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jalur_masuk(Request $request)
    {
        return $this->getReferensiData(
            'jalur_masuk',
            ['id_jalur_masuk', 'nama_jalur_masuk'],
            'id_jalur_masuk',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_aktivitas_mahasiswa(Request $request)
    {
        return $this->getReferensiData(
            'jenis_aktivitas_mahasiswa',
            ['id_jenis_aktivitas_mahasiswa', 'nama_jenis_aktivitas_mahasiswa'],
            'id_jenis_aktivitas_mahasiswa',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_beasiswa(Request $request)
    {
        return $this->getReferensiData(
            'jenis_beasiswa',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_daftar(Request $request)
    {
        return $this->getReferensiData(
            'jenis_daftar',
            ['id_jenis_daftar', 'nama_jenis_daftar'],
            'id_jenis_daftar',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_diklat(Request $request)
    {
        return $this->getReferensiData(
            'jenis_diklat',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_dokumen(Request $request)
    {
        return $this->getReferensiData(
            'jenis_dokumen',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_evaluasi(Request $request)
    {
        return $this->getReferensiData(
            'jenis_evaluasi',
            ['id_jenis_evaluasi', 'nama_jenis_evaluasi'],
            'id_jenis_evaluasi',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_keluar(Request $request)
    {
        return $this->getReferensiData(
            'jenis_keluar',
            ['id_jenis_keluar', 'jenis_keluar', 'apa_mahasiswa'],
            'id_jenis_keluar',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_kepanitiaan(Request $request)
    {
        return $this->getReferensiData(
            'jenis_kepanitiaan',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_kesejahteraan(Request $request)
    {
        return $this->getReferensiData(
            'jenis_kesejahteraan',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_mata_kuliah(Request $request)
    {
        return $this->getReferensiData(
            'jenis_mata_kuliah',
            ['id_jenis_mata_kuliah', 'nama_jenis_mata_kuliah'],
            'id_jenis_mata_kuliah',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_pekerjaan(Request $request)
    {
        return $this->getReferensiData(
            'jenis_pekerjaan',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_penghargaan(Request $request)
    {
        return $this->getReferensiData(
            'jenis_penghargaan',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_prestasi(Request $request)
    {
        return $this->getReferensiData(
            'jenis_prestasi',
            ['id_jenis_prestasi', 'nama_jenis_prestasi'],
            'id_jenis_prestasi',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_publikasi(Request $request)
    {
        return $this->getReferensiData(
            'jenis_publikasi',
            ['id', 'nama'],
            'id',
            'Data tidak ditemukan.'
        );
    }

    public function get_jenis_sertifikasi(Request $request)
    {
        return $this->getReferensiData(
            'jenis_sertifikasi',
            ['id_jenis_sertifikasi', 'nama_jenis_sertifikasi'],
            'id_jenis_sertifikasi',
            'Data tidak ditemukan.'
        );
    }

    
}


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
