<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LppmController extends Controller
{
    /**
     * Fungsi umum untuk mengambil data dengan limit dan offset.
     */
    private function getData(
        string $table,
        array $columns,
        array $orderBy = [],
        array $jsonColumns = []
    ): JsonResponse {
        $limit = request()->integer('limit', 100);
        $offset = request()->integer('offset', 0);

        /*
         * Limit minimal 1 dan maksimal 1.000 data.
         */
        $limit = min(max($limit, 1), 1000);
        $offset = max($offset, 0);

        $query = DB::connection('pdunsri')
            ->table($table);

        $totalData = $query->count();

        $query->select($columns);

        foreach ($orderBy as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        $data = $query
            ->offset($offset)
            ->limit($limit)
            ->get();

        /*
         * Mengubah field JSON dari string menjadi array.
         */
        if (!empty($jsonColumns)) {
            $data->transform(function ($item) use ($jsonColumns) {
                foreach ($jsonColumns as $column) {
                    if (
                        isset($item->{$column})
                        && is_string($item->{$column})
                    ) {
                        $decoded = json_decode(
                            $item->{$column},
                            true
                        );

                        $item->{$column} =
                            json_last_error() === JSON_ERROR_NONE
                                ? $decoded
                                : $item->{$column};
                    }
                }

                return $item;
            });
        }

        $totalPage = $totalData > 0
            ? (int) ceil($totalData / $limit)
            : 0;

        $currentPage = $totalData > 0
            ? (int) floor($offset / $limit) + 1
            : 0;

        return response()->json([
            'limit'        => $limit,
            'offset'       => $offset,
            'totalData'    => $totalData,
            'returnedData' => $data->count(),
            'totalPage'    => $totalPage,
            'currentPage'  => $currentPage,
            'data'         => $data,
        ], 200);
    }

    /**
     * Seluruh data desa binaan.
     */
    public function desa_binaan(): JsonResponse
    {
        return $this->getData(
            'lppm_desa_binaan',
            [
                'id_desa_binaan',
                'nama',
                'kecamatan',
                'kabupaten_kota',
                'max_usulan',
                'min_usulan',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            [
                'kabupaten_kota' => 'asc',
                'kecamatan'      => 'asc',
                'nama'           => 'asc',
            ]
        );
    }

    /**
     * Data penelitian dosen.
     */
    public function penelitian_dosen(): JsonResponse
    {
        return $this->getData(
            'lppm_penelitian_dosen',
            [
                'id_ketua',
                'nama_skema',
                'anggota_non_dosen',
                'judul',
                'tahun',
            ],
            [
                'tahun' => 'desc',
                'judul' => 'asc',
            ],
            [
                'anggota_non_dosen',
            ]
        );
    }

    /**
     * Data pengabdian dosen.
     */
    public function pengabdian_dosen(): JsonResponse
    {
        return $this->getData(
            'lppm_pengabdian_dosen',
            [
                'id_ketua',
                'nama_skema',
                'anggota_non_dosen',
                'judul',
                'tahun',
            ],
            [
                'tahun' => 'desc',
                'judul' => 'asc',
            ],
            [
                'anggota_non_dosen',
            ]
        );
    }
}