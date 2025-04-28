<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPemesanan extends Model
{
    use HasFactory;

    protected $table = "riwayat_pemesanan";

    protected $fillable = [
        'metode',
        'kasir_id',
        'total_item',
        'total_harga',
        'bayar',
        'kembalian',
        'tanggal_pemesanan',
    ];

    // Tambahkan relasi
    public function kasir(){
        return $this->belongsTo(User::class, 'kasir_id');
    }
    public function detail_produk()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_pembelian');
    }
}
