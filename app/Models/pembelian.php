<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\Kategori;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    //Ambil semua data pembelian
    public function index()
    {
        $data = Pembelian::orderBy('created_at', 'desc')->get();
        return response()->json($data);
    }

    //Simpan data pembelian
    public function store(Request $request)
    {
        $request->validate([
            'total_item' => 'required|integer',
            'total_harga' => 'required|integer',
            'bayar' => 'required|integer',
            'metode' => 'required|in:Cash,Qris',
        ]);

        $pembelian = Pembelian::create([
            'total_item' => $request->total_item,
            'total_harga' => $request->total_harga,
            'bayar' => $request->bayar,
            'metode' => $request->metode,
        ]);

        return response()->json($pembelian, 201);
    }

    //  Hapus data pembelian
    public function destroy($id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $pembelian->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    // Tambahan: Ambil data produk dan kategori untuk halaman pembelian
    public function dataProduk()
    {
        $kategori = Kategori::with(['produk' => function ($query) {
            $query->where('status', 'tersedia');
        }])->get();

        return response()->json($kategori);
    }

        public function detail_pemesanan()
    {
        return $this->hasMany(Detail_pemesanan::class, 'id_pembelian');
    }
}
