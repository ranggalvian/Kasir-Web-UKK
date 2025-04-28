<template>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow">
      <h2 class="text-2xl font-semibold mb-4">Pembayaran</h2>
  
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Total Harga</label>
        <div class="mt-1 text-lg font-semibold">Rp{{ totalHarga }}</div>
      </div>
  
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
        <div class="mt-1">{{ metodePembayaran }}</div>
      </div>
  
      <div v-if="metodePembayaran === 'cash'" class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Uang Diberikan</label>
        <input
          type="number"
          v-model.number="bayar"
          class="mt-1 block w-full border border-gray-300 p-2 rounded"
          placeholder="Masukkan jumlah uang"
        />
        <div v-if="bayar >= totalHarga" class="text-green-600 mt-1">Kembalian: Rp{{ kembalian }}</div>
        <div v-else class="text-red-500 mt-1">Uang tidak cukup</div>
      </div>
  
      <div v-else-if="metodePembayaran === 'qris'" class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Scan QRIS untuk membayar</label>
        <div class="mt-2">
         
          <img src="/img/qris-placeholder.png" alt="QRIS" class="w-40 h-40 object-contain" />
        </div>
      </div>
  
      <button
        class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 mt-4"
        :disabled="metodePembayaran === 'cash' && bayar < totalHarga"
        @click="submitPembelian"
      >
        Konfirmasi & Simpan
      </button>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import axios from '@/libs/axios'
  
  const route = useRoute()
  const router = useRouter()
  
  const metodePembayaran = route.query.metode || 'cash'
  const totalHarga = ref(0)
  const bayar = ref(0)
  
  
  const keranjang = ref([])
  
  onMounted(() => {
    
    const dataKeranjang = localStorage.getItem('keranjang')
    const total = localStorage.getItem('total_harga')
  
    if (dataKeranjang) {
      keranjang.value = JSON.parse(dataKeranjang)
    }
    if (total) {
      totalHarga.value = parseInt(total)
    }
  })
  
  const kembalian = computed(() => bayar.value - totalHarga.value)
  
  const submitPembelian = async () => {
    try {
      const payload = {
        total_item: keranjang.value.reduce((acc, item) => acc + item.qty, 0),
        total_harga: totalHarga.value,
        bayar: metodePembayaran === 'cash' ? bayar.value : totalHarga.value,
        metode: metodePembayaran,
        items: keranjang.value.map(item => ({
          id_produk: item.id,
          qty: item.qty,
          harga: item.harga
        }))
      }
  
    //   await axios.post('/transaksi/pembelian', payload)
  
    //   alert('Pembelian berhasil disimpan!')
    //   localStorage.removeItem('keranjang')
    //   localStorage.removeItem('total_harga')
    //   router.push('/')
    // } catch (err) {
    //   console.error('Gagal menyimpan pembelian:', err)
    //   alert('Terjadi kesalahan saat menyimpan pembelian.')
    // }
  }
  </script>
  