<template>
  <!-- Modal Konfirmasi Pembayaran -->
  <div v-if="showModal">
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Konfirmasi Pembayaran</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <div class="modal-body">
            <div class="d-flex">
              <h6 class="me-3">Kasir : </h6>
              <span>{{ kasir }}</span>
            </div>
            <h6 class="fw-bold">Daftar Produk:</h6>
            <ul class="list-group list-group-flush mb-3">
              <li v-for="(item, index) in pembelian.detail_produk" :key="index" class="list-group-item d-flex justify-content-between p-2">
                <span>{{ item.nama_produk }} x{{ item.qty }}</span>
                <span>{{ formatRupiah(item.harga * item.qty) }}</span>
              </li>
            </ul>

            <h6 class="fw-bold">Pembayaran:</h6>
            <ul class="list-group list-group-flush">
              <li v-for="(info, key) in pembayaranInfo" :key="key" class="list-group-item d-flex justify-content-between p-2">
                <span>{{ info.label }}</span>
                <span>{{ info.value }}</span>
              </li>
            </ul>
          </div>

          <div class="modal-footer justify-content-end gap-2">
            <button type="button" class="btn btn-success btn-sm" :disabled="isLoading" @click="konfirmasi_pembayaran">OK</button>
            <button type="button" class="btn btn-danger btn-sm" @click="closeModal">Batal</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  </div>

  <!-- Modal Payment Success untuk QRIS -->
  <!-- <div v-if="showSuccessModal">
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
          <h5 class="mb-3">Pembayaran QRIS Berhasil</h5>
          <p class="text-muted">Transaksi telah berhasil dan data masuk ke riwayat pemesanan.</p>
          <div class="d-grid gap-2 mt-3">
            <button class="btn btn-primary btn-sm" @click="printStruk">Cetak Struk</button>
            <button class="btn btn-success btn-sm" @click="kembali">Kembali</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  </div> -->

  <!-- Struk Pembayaran -->
  <div v-if="showPrint" class="container monospace py-4 d-flex justify-content-center">
    <div class="p-3" style="width: 300px;">
      <div class="d-grid gap-2 mt-3">
        <img src="/media/iconWarung.png" class="mx-auto d-block" style="width: 100px;">
      </div>
      <h5 class="text-center mb-3 fw-bold">Berkah Warung Emak </h5>
      <div class="text-center" style="font-family: monospace;">
        <p>Jl.Salman Aselole</p>
        <p> No. Telp 0851-9834-2110</p>
      </div>
      <hr>
      <div class="text-center small mb-2">
        <div>Kasir: {{ kasir }}</div>
        <div>Waktu Pemesanan:
          <p>{{ pembelian.tanggal_pemesanan }}</p>
        </div>
      </div>
      <hr>
      <div class="text mb-2" style="font-weight: 500;"> Menu yang di pesan:  </div>
      <div class="small mb-2" v-for="(item, index) in pembelian.detail_produk" :key="index">
        <div class="d-flex justify-content-between">
          <span>{{ item.nama_produk }} x{{ item.qty }}</span>
          <span>{{ formatRupiah(item.harga * item.qty) }}</span>
        </div>
      </div>
      <hr>
      <div class="small mb-2" v-for="(info, key) in pembayaranInfo" :key="key">
        <div class="d-flex justify-content-between">
          <span>{{ info.label }}</span>
          <span>{{ info.value }}</span>
        </div>
      </div>
      <hr>
      <div class="text-center mb-3 ">
        <p class="mb-0 fw-bold">--Terima Kasih Sudah Memesan--</p>
        <p class="text-center d-flex justify-content-between font-weight-light small mb-5">
          Menerima saran dan keritikan kecuali menerima kenyataan kalo dia sudah dengan lain nya
        </p>
        <p>
          (Hub. No 0851-9834-2110)
        </p>
      </div>
      <div class="d-grid gap-2 mt-3">
        <button class="btn btn-primary btn-sm" @click="printStruk">Cetak Struk</button>
        <button class="btn btn-danger btn-sm" @click="kembali">Kembali</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from "@/stores/auth";
import axios from 'axios'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()
const store = useAuthStore()
const kasir = store.user.name
const isLoading = ref(false)

const pembelian = ref({
  metode: route.query.metode || '',
  total_item: route.query.total_item || 0,
  total_harga: route.query.total_harga || 0,
  bayar: route.query.bayar || 0,
  kembalian: route.query.kembalian || 0,
  tanggal_pemesanan: route.query.tanggal_pemesanan || '',
  detail_produk: JSON.parse(route.query.detail_produk || '[]')
})

const showModal = ref(false)
const showPrint = ref(false)
const showSuccessModal = ref(false)

const pembayaranInfo = computed(() => [
  { label: 'Metode', value: pembelian.value.metode },
  { label: 'Total Item', value: pembelian.value.total_item },
  { label: 'Total Harga', value: formatRupiah(pembelian.value.total_harga) },
  { label: 'Jumlah Bayar', value: formatRupiah(pembelian.value.bayar) },
  { label: 'Kembalian', value: formatRupiah(pembelian.value.kembalian) },
  { label: 'Waktu Pemesanan', value: pembelian.value.tanggal_pemesanan }
])

onMounted(async () => {
  if (pembelian.value.metode.toLowerCase() === 'qris') {
    const result = await Swal.fire({
      title: 'Pembayaran Berhasil Silahkan Cetak Struk',
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Lanjutkan',
      cancelButtonText: 'Batalkan',
      allowOutsideClick: false
    })

    if (result.isConfirmed) {
      konfirmasi_pembayaran();
    } else {
      router.back()
    }
  } else {
    showModal.value = true
  }
})

const closeModal = () => {
  showModal.value = false
  router.back()
}

const konfirmasi_pembayaran = async () => {
  isLoading.value = true
  try {
    await axios.post('/master/riwayat-pemesanan', {
      kasir: store.user.id,
      metode: pembelian.value.metode,
      total_item: pembelian.value.total_item,
      total_harga: pembelian.value.total_harga,
      bayar: pembelian.value.bayar,
      kembalian: pembelian.value.kembalian,
      tanggal_pemesanan: pembelian.value.tanggal_pemesanan,
      detail_produk: pembelian.value.detail_produk
    })

    showModal.value = false
    if (pembelian.value.metode.toLowerCase() === 'qris') {
      showPrint.value = true
    } else {
      showPrint.value = true
    }
  } catch (error) {
    console.error('Gagal menyimpan pembayaran:', error)
    Swal.fire({
      icon: 'error',
      title: 'Oops!',
      text: 'Gagal menyimpan pembayaran!'
    })
  } finally {
    isLoading.value = false
  }
}

const kembali = () => {
  showSuccessModal.value = false
  showPrint.value = false
  router.back()
}

const printStruk = () => {
  showSuccessModal.value = false
  showPrint.value = true

   setTimeout(() => {
    window.print()
  }, 300)
}


const formatRupiah = (value) => {
  if (!value) return 'Rp0'
  return 'Rp' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
</script>

<style scoped>
@page {
  padding: 0;
  margin: 0;
}
@media print {
  .btn {
    display: none;
  }
}
.monospace {
  font-family: monospace;
  font-size: 15.5px;
}
</style>
