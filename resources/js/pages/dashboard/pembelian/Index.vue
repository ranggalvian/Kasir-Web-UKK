<template>
  <div class="container-fluid bg-light p-4">
    <div class="row">
      <!-- Kolom Kategori & Produk -->
      <div class="col-lg-8">
        <!-- Kategori -->
        <div class="d-flex mb-4 overflow-auto gap-2">
          <div
            v-for="kat in kategori"
            :key="kat"
            @click="kategoriAktif = kat"
            :class="[
              'px-3 py-2 border rounded text-center flex-shrink-0',
              kategoriAktif === kat
                ? 'border-primary bg-primary text-white fw-semibold'
                : 'bg-white text-dark'
            ]"
            style="min-width: 100px; cursor: pointer;"
          >
            <span class="small">{{ kat }}</span>
          </div>
        </div>

        <!-- Produk -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
          <div
            v-for="item in produkFiltered"
            :key="item.id_produk"
            class="col"
            @click="handleKlikProduk(item)"  
          >
            <div
              class="card text-center h-100 p-3 d-flex flex-column justify-content-between shadow-sm"
              :class="{
                'opacity-50 bg-light text-muted': item.ketersediaan !== 'Tersedia', 
                'cursor-pointer': true
              }"
            >
              <div>
                <h5 class="fw-bold">{{ item.nama_produk }}</h5>
              </div>
              <div>
                <p class="fw-semibold" :class="item.ketersediaan === 'Tersedia' ? 'text-success' : 'text-muted'">
                  Rp{{ item.harga }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order List -->
      <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="bg-white p-4 rounded shadow d-flex flex-column justify-content-between h-100">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="fw-bold mb-0">Menu yang Di Pesan</h4>
              <button class="btn btn-link text-primary p-0 small" @click="keranjang = []">Hapus Semua</button>
            </div>

            <div v-if="keranjang.length === 0" class="text-center text-muted py-5">Belum ada pesanan</div>

            <div
              v-for="item in keranjang"
              :key="item.id"
              class="d-flex justify-content-between align-items-center border-bottom py-2"
            >
              <div class="d-flex align-items-center gap-2">
                <div>
                  <div class="fw-medium">{{ item.nama_produk }}</div>
                  <div class="text-muted small">Rp{{ item.harga }}</div>
                </div>
              </div>
              <div class="d-flex align-items-center gap-1">
                <button @click="kurangiItem(item)" class="btn btn-sm btn-outline-secondary">-</button>
                <span>{{ item.qty }}</span>
                <button @click="tambahItem(item)" class="btn btn-sm btn-outline-secondary">+</button>
                <button @click="hapusItem(item)" class="btn btn-sm btn-outline-danger ms-2">🗑</button>
              </div>
            </div>
          </div>

          <!-- Total & Bayar -->
          <div class="mt-4">
            <div class="d-flex justify-content-between font-weight-light small">
              <span>Total Item</span>
              <span>{{ totalItem }}</span>
            </div>
            <div class="d-flex justify-content-between font-weight-light small mb-5">
              <span>Total Harga</span>
              <span>Rp{{ totalHarga }}</span>
            </div>

            <!-- Input Jumlah Bayar -->
            <!-- Input Jumlah Bayar -->
            <div v-if="metodePembayaran === 'cash'" class="mb-3">
              <label for="bayarJumlah" class="form-label fw-medium">Jumlah Bayar</label>
              <input
                id="bayarJumlah"
                v-model="bayarJumlah"
                type="text"
                inputmode="numeric"
                placeholder="Masukkan jumlah bayar"
                class="form-control"
                :readonly="metodePembayaran === 'qris'"
                :class="{
                  'is-invalid':
                    metodePembayaran === 'cash' &&
                    bayarJumlahRaw < totalHarga &&
                    bayarJumlahRaw > 0
                }"
              />
              <div
                v-if="metodePembayaran === 'cash' && bayarJumlahRaw < totalHarga && bayarJumlahRaw > 0"
                class="invalid-feedback"
              >
                Jumlah bayar kurang dari total harga!
              </div>
            </div>


            <!-- Kembalian hanya untuk cash -->
            <div
              v-if="metodePembayaran === 'cash' && bayarJumlah >= totalHarga && totalHarga > 0"
              class="mb-3"
            >
              <div class="d-flex justify-content-between">
                <span class="fw-medium">Kembalian</span>
                <span class="text-success fw-bold">Rp{{ kembalian }}</span>
              </div>
            </div>

            <!-- Pilihan Metode Pembayaran -->
            <div class="mb-3">
              <label class="form-label fw-medium">Pembayaran Melalui</label>
              <div class="d-flex gap-2">
                <button
                  @click="metodePembayaran = 'cash'"
                  :class="[
                    'btn btn-sm flex-fill',
                    metodePembayaran === 'cash' ? 'btn-primary' : 'btn-outline-secondary'
                  ]"
                >Cash</button>
                <button
                  @click="metodePembayaran = 'qris'"
                  :class="[
                    'btn btn-sm flex-fill',
                    metodePembayaran === 'qris' ? 'btn-primary' : 'btn-outline-secondary'
                  ]"
                >QRIS</button>
                <button class="btn btn-sm btn-outline-secondary flex-fill" disabled>Card</button>
              </div>
            </div>

            <button
              class="btn btn-primary w-100 fw-semibold"
              @click="bayar"
              :disabled="keranjang.length === 0 || (metodePembayaran === 'cash' && bayarJumlah < totalHarga)"
            >
              Pesan
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '@/libs/axios'
import { useRouter } from 'vue-router'
import { toast } from "vue3-toastify"

const router = useRouter()

const produk = ref([])
const kategori = ref(['Semua','Minuman','Makanan'])
const kategoriAktif = ref('Semua')
const keranjang = ref([])
const metodePembayaran = ref('cash')

// raw value (number) untuk input bayar
const bayarJumlahRaw = ref(0)

// format Rupiah otomatis saat diketik
const bayarJumlah = computed({
  get() {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(bayarJumlahRaw.value)
  },
  set(val) {
    const numeric = parseInt(val.replace(/[^0-9]/g, '')) || 0
    bayarJumlahRaw.value = numeric
  }
})

onMounted(async () => {
  try {
    const resProduk = await axios.get('/master/produk')
    produk.value = resProduk.data

    const resKategori = await axios.post('/master/kategori')
    kategori.value = ['Semua', ...resKategori.data.data.map(k => k.nama_kategori)]
  } 
  catch (error) {
    console.error('Gagal mengambil data produk/kategori:', error)
  }
})

const produkFiltered = computed(() => {
  if (kategoriAktif.value === 'Semua') {
    return produk.value
  } else {
    return produk.value.filter(p => p.kategori.nama_kategori === kategoriAktif.value)
  }
})

const handleKlikProduk = (item) => {
  if (item.ketersediaan === 'Tersedia') {
    tambahKeKeranjang(item)
  } else {
    toast.error("Menu Tidak Tersedia")
  }
}

const tambahKeKeranjang = (item) => {
  const found = keranjang.value.find(i => i.id_produk === item.id_produk)
  if (found) {
    found.qty++
  } else {
    keranjang.value.push({ ...item, qty: 1 })
  }
}

const tambahItem = (item) => {
  const found = keranjang.value.find(i => i.id_produk === item.id_produk)
  if (found) found.qty++
}

const kurangiItem = (item) => {
  const found = keranjang.value.find(i => i.id_produk === item.id_produk)
  if (found) {
    found.qty--
    if (found.qty <= 0) hapusItem(item)
  }
}

const hapusItem = (item) => {
  keranjang.value = keranjang.value.filter(i => i.id_produk !== item.id_produk)
}

const totalHarga = computed(() =>
  keranjang.value.reduce((sum, item) => sum + item.qty * item.harga, 0)
)

const totalItem = computed(() =>
  keranjang.value.reduce((sum, item) => sum + item.qty, 0)
)

const kembalian = computed(() => {
  if (bayarJumlahRaw.value >= totalHarga.value) {
    return bayarJumlahRaw.value - totalHarga.value
  }
  return 0
})

const formatTanggalIndonesia = (date) => {
  const d = new Date(date)
  const tanggal = String(d.getDate()).padStart(2, '0')
  const bulan = String(d.getMonth() + 1).padStart(2, '0')
  const tahun = d.getFullYear()
  const jam = String(d.getHours()).padStart(2, '0')
  const menit = String(d.getMinutes()).padStart(2, '0')

  return `${tanggal}-${bulan}-${tahun} ${jam}:${menit}`
}

const setMetode = () => {
  if (metodePembayaran.value === 'qris') {
    bayarJumlahRaw.value = totalHarga.value
  }
}

const bayar = () => {
  setMetode()
  if (metodePembayaran.value === 'cash' && bayarJumlahRaw.value < totalHarga.value) {
    alert('Jumlah bayar kurang dari total harga!')
    return
  }

  const tanggalPemesanan = formatTanggalIndonesia(new Date())

  router.push({
    name: 'dashboard.pembelian.form',
    query: {
      metode: metodePembayaran.value,
      total_item: totalItem.value,
      total_harga: totalHarga.value,
      bayar: bayarJumlahRaw.value,
      kembalian: kembalian.value,
      tanggal_pemesanan: tanggalPemesanan,
      detail_produk: JSON.stringify(keranjang.value)
    }
  })
}

</script>
