<?php

namespace App\Http\Controllers;

use App\Models\Detail_pemesanan;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DetailPemesananRequest;

class DetailPemesananController extends Controller
{
    // Tampilkan semua pembelian
    public function index(): JsonResponse
    {
        $data = Pembelian::orderBy('created_at', 'desc')->get();
        return response()->json($data);
    }

    // Simpan pembelian baru
    public function store(DetailPemesananRequest $request): JsonResponse
    {
        $pembelian = Pembelian::create($request->validated());

        return response()->json($pembelian, 201);
    }

    // Hapus pembelian
    public function destroy($id): JsonResponse
    {
        $pembelian = Pembelian::findOrFail($id);
        $pembelian->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
