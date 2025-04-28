<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RiwayatPemesanan; // pastikan namespace ini
use App\Models\Produk;

class DetailPemesanan extends Model
{
    use HasFactory;

    protected $table = 'detail_pemesanan'; //di ubah

    protected $fillable = [
        'id_pembelian',
        'id_produk',
        'qty',
        'harga_satuan',
        'subtotal',
    ];

    /**
     * Relasi ke header riwayat_pemesanan
     */
    public function riwayatPemesanan()
    {
        return $this->belongsTo(RiwayatPemesanan::class, 'id_pembelian');
    }

    /**
     * Relasi ke Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
