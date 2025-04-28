<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPemesanan;
use App\Models\DetailPemesanan;
use Illuminate\Http\Request;

class RiwayatPemesananController extends Controller
{
    // Menyimpan riwayat pemesanan baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kasir' => 'required',
            'metode' => 'required|string',
            'total_item' => 'required|integer',
            'total_harga' => 'required|integer',
            'bayar' => 'required|integer',
            'kembalian' => 'required|integer',
            'detail_produk' => 'required'
        ]);

        
        $riwayat = RiwayatPemesanan::create([
            'kasir_id' => $validated['kasir'],
            'metode' => $validated['metode'],
            'total_item' => $validated['total_item'],
            'total_harga' => $validated['total_harga'],
            'bayar' => $validated['bayar'],
            'kembalian' => $validated['kembalian'],
             // simpan array jadi json
        ]);
        foreach($validated['detail_produk'] as $item){
            DetailPemesanan::create([
                'id_pembelian' => $riwayat->id,
                'id_produk' => $item['id_produk'],
                'qty' => $item['qty'],
                'harga_satuan' => $item['harga'],    
                'subtotal' => $item['harga'] * $item['qty'],
            ]);
        }
        

        return response()->json([
            'message' => 'Riwayat pemesanan berhasil disimpan!',
            'data' => $riwayat,
        ], 201);
    }

    // Menampilkan daftar riwayat pemesanan
    public function index(Request $request)
    {
        $query = RiwayatPemesanan::with(['kasir','detail_produk.produk'])->orderBy('created_at', 'desc');//di ubah
       
    
        // Filter by tanggal kalau ada
        if ($request->has('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }
    
        $riwayat = $query->get();
    
        return response()->json([
            'message' => 'Berhasil mengambil data riwayat pemesanan',
            'data' => $riwayat,
        ]);
    }
}
