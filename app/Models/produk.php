<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk'; // Sesuaikan dengan nama tabel
    protected $primaryKey = 'id_produk'; // Sesuaikan dengan primary key
    protected $fillable = ['id_kategori', 'nama_produk', 'harga','stock','ketersediaan'];

    // Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
}
