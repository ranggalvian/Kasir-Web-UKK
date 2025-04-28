export interface RiwayatPemesanan {
  id?: number
  metode: string
  total_item: number
  total_harga: number
  bayar: number
  kembalian: number
  tanggal_pemesanan: string
  detail_produk: DetailProduk[]   // daftar produk yang dibeli
  created_at?: string
  updated_at?: string
}

export interface DetailProduk {
  id?: number
  nama_produk: string
  harga: number
  qty: number
}
