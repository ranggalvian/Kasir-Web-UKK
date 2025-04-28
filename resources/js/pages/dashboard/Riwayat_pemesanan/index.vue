<template>
  <div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
      <h1 class="h3 fw-bold mb-0">Riwayat Pemesanan</h1>

      <div class="input-group w-auto">
        <input 
          type="date" 
          v-model="filterTanggal" 
          class="form-control"
        />
        <button 
          @click="fetchRiwayat" 
          class="btn btn-primary"
          type="button"
        >
          Filter
        </button>
      </div>
    </div>

    <!-- Tombol Export PDF -->
    <div class="d-flex justify-content-end gap-2 mb-3">
      <button class="btn btn-danger" @click="exportToPDF">Export PDF</button>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col" class="text-center">No</th>
                <th scope="col">Tanggal</th>
                <th scope="col">Metode</th>
                <th scope="col">Kasir</th>
                <th scope="col" class="text-center">Menu</th>
                <th scope="col" class="text-center">Total Item</th>
                <th scope="col">Total Harga</th>
                <th scope="col">Bayar</th>
                <th scope="col">Kembalian</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(pesanan, index) in riwayat" :key="pesanan.id">
                <td class="text-center">{{ index + 1 }}</td>
                <td>{{ formatTanggal(pesanan.created_at) }}</td>
                <td>
                  <span 
                    class="badge text-uppercase"
                    :class="pesanan.metode === 'cash' ? 'bg-success' : 'bg-info'"
                  >
                    {{ pesanan.metode }}
                  </span>
                </td>
                <!-- Tampilkan nama kasir -->
                <td>{{ pesanan.kasir?.name || '-' }}</td>
                <td class="text-center">
                  <button 
                    class="btn btn-sm btn-outline-primary w-100" 
                    @click="toggleDetail(pesanan.id)"
                  >
                    Menu pesanan
                  </button>

                  <ul 
                    v-if="selectedId === pesanan.id" 
                    class="list-unstyled mt-2 small text-start"
                  >
                    <li 
                      v-for="detail in pesanan.detail_produk" 
                      :key="detail.id"
                      class="border-bottom py-1 d-flex justify-content-between"
                    >
                      <div>
                        {{ detail.produk?.nama_produk || '-' }}
                        <span class="text-muted">(x{{ detail.qty }})</span>
                      </div>
                      <div class="text-end fw-semibold">
                        {{ formatRupiah((detail.produk?.harga || 0) * detail.qty) }}
                      </div>
                    </li>
                  </ul>
                </td>
                <td class="text-center fw-bold">{{ pesanan.total_item }}</td>
                <td>{{ formatRupiah(pesanan.total_harga) }}</td>
                <td>{{ formatRupiah(pesanan.bayar) }}</td>
                <td>{{ formatRupiah(pesanan.kembalian) }}</td>
              </tr>

              <tr v-if="riwayat.length === 0">
                <td colspan="9" class="text-center py-5 text-muted">
                  <i class="bi bi-emoji-frown" style="font-size: 2rem;"></i><br />
                  Belum ada pemesanan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from '@/libs/axios'
import { ref, onMounted } from 'vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

const riwayat = ref([])
const filterTanggal = ref('')
const selectedId = ref(null)

const fetchRiwayat = async () => {
  try {
    const params = {}
    if (filterTanggal.value) {
      params.tanggal = filterTanggal.value
    }
    const response = await axios.get('/master/riwayat-pemesanan', { params })
    riwayat.value = response.data.data
  } catch (error) {
    console.error('Gagal mengambil riwayat:', error)
    alert('Gagal mengambil data riwayat!')
  }
}

const toggleDetail = (id) => {
  selectedId.value = selectedId.value === id ? null : id
}

onMounted(() => {
  fetchRiwayat()
})

const formatRupiah = (value) => {
  if (!value) return 'Rp0'
  return 'Rp' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

const formatTanggal = (tanggal) => {
  if (!tanggal) return '-'
  const date = new Date(tanggal)
  return date.toLocaleDateString('id-ID')
}

// Fungsi export PDF termasuk nama kasir
const exportToPDF = () => {
  const doc = new jsPDF()
  doc.text('Laporan Penjualan', 14, 10)

  const head = [['No', 'Tanggal', 'Metode', 'Kasir', 'Total Item', 'Total Harga', 'Bayar', 'Kembalian']]
  const body = []

  riwayat.value.forEach((item, index) => {
    // Baris transaksi utama
    body.push([
      index + 1,
      formatTanggal(item.created_at),
      item.metode.toUpperCase(),
      item.kasir?.name || '-',      // <-- nama kasir
      item.total_item,
      formatRupiah(item.total_harga),
      formatRupiah(item.bayar),
      formatRupiah(item.kembalian),
    ])

    // Detail menu
    item.detail_produk.forEach((detail) => {
      const namaProduk = detail.produk?.nama_produk || '-'
      const qty = detail.qty
      const subtotal = formatRupiah((detail.produk?.harga || 0) * qty)

      body.push([
        '', '', '', { content: `- ${namaProduk} (x${qty})`, colSpan: 3 }, subtotal, ''
      ])
    })
  })

  autoTable(doc, {
    head,
    body,
    startY: 20,
    styles: { fontSize: 9 },
    headStyles: { fillColor: [41, 128, 185] },
    columnStyles: {
      0: { cellWidth: 10 },
      1: { cellWidth: 25 },
      2: { cellWidth: 20 },
      3: { cellWidth: 30 },
      4: { cellWidth: 25 },
      5: { cellWidth: 25 },
      6: { cellWidth: 25 },
      7: { cellWidth: 25 },
    },
  })

  doc.save(`laporan-penjualan-${new Date().toISOString().slice(0, 10)}.pdf`)
}
</script>

<style scoped>
.table-row:hover {
  background-color: #f8f9fa;
}
</style>
