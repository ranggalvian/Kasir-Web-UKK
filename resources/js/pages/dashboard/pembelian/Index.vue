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
            @click="tambahKeKeranjang(item)"
          >
            <div class="card text-center h-100 p-3 d-flex flex-column justify-content-between shadow-sm" style="cursor: pointer;">
              <div>
                <h5 class="fw-bold">{{ item.nama_produk }}</h5>
              </div>
              <div>
                <p class="text-success fw-semibold">Rp{{ item.harga }}</p>
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
            <div class="d-flex justify-content-between text-muted small">
              <span>Total Item</span>
              <span>{{ totalItem }}</span>
            </div>
            <div class="d-flex justify-content-between text-muted small mb-3">
              <span>Total Harga</span>
              <span>Rp{{ totalHarga }}</span>
            </div>

            <!-- Input Jumlah Bayar -->
            <div class="mb-3">
              <label for="bayarJumlah" class="form-label fw-medium">Jumlah Bayar</label>
              <input
                id="bayarJumlah"
                v-model.number="bayarJumlah"
                type="number"
                placeholder="Masukkan jumlah bayar"
                class="form-control"
                :class="{ 'is-invalid': bayarJumlah < totalHarga && bayarJumlah > 0 }"
              />
              <div v-if="bayarJumlah < totalHarga && bayarJumlah > 0" class="invalid-feedback">
                Jumlah bayar kurang dari total harga!
              </div>
            </div>

            <!-- Tampilkan Kembalian -->
            <div v-if="bayarJumlah >= totalHarga && totalHarga > 0" class="mb-3">
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
              :disabled="keranjang.length === 0 || bayarJumlah < totalHarga"
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

const router = useRouter()

const produk = ref([])
const kategori = ref(['Semua'])
const kategoriAktif = ref('Semua')
const keranjang = ref([])
const metodePembayaran = ref('cash')
const bayarJumlah = ref(0)

onMounted(async () => {
  try {
    const resProduk = await axios.post('/master/produk')
    produk.value = resProduk.data.data

    const resKategori = await axios.post('/master/kategori')
    kategori.value = ['Semua', ...resKategori.data.data.map(k => k.nama_kategori)]
  } 
  catch (error) {
    console.error('Gagal mengambil data produk/kategori:', error)
  }
})

const produkFiltered = computed(() => {
  return kategoriAktif.value === 'Semua'
    ? produk.value
    : produk.value.filter(p => p.nama_kategori === kategoriAktif.value)
})

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
  if (bayarJumlah.value >= totalHarga.value) {
    return bayarJumlah.value - totalHarga.value
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

const bayar = () => {
  if (bayarJumlah.value < totalHarga.value) {
    alert('Jumlah bayar kurang dari total harga!')
    return
  }

  const metode = metodePembayaran.value === 'cash' ? 'cash' : 'qris'
  const tanggalPemesanan = formatTanggalIndonesia(new Date())

  router.push({
    name: 'dashboard.pembelian.form',
    query: {
      metode: metode,
      total_item: totalItem.value,
      total_harga: totalHarga.value,
      bayar: bayarJumlah.value,
      kembalian: kembalian.value,
      tanggal_pemesanan: tanggalPemesanan,
      detail_produk: JSON.stringify(keranjang.value)
    }
  })
}
</script>
