<?php

namespace App\Http\Controllers\Api\Feeder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferensiBUController extends Controller
{
    private function getReferensiData($table, $columns, $orderBy)
    {
        try {

            $data = DB::connection('pdunsri')
                ->table($table)
                ->select($columns)
                ->orderBy($orderBy, 'asc')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.',
                    'totalData' => 0,
                    'totalRow' => 0,
                    'data' => []
                ], 404);
            }

            return response()->json([
                'totalData' => $data->count(),
                'totalRow' => count((array) $data->first()),
                'data' => $data
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'table' => $table
            ], 500);
        }
    }

    public function get_prodi()
    {
        return $this->getReferensiData(
            'program_studi',
            [
                'id_prodi',
                'kode_program_studi',
                'nama_jenjang_pendidikan',
                'status',
                'id_jenjang_pendidikan',
                'nama_program_studi'
            ],
            'nama_jenjang_pendidikan'
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
