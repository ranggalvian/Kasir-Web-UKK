<template>
  
    <!-- Tombol Export -->
    <div class="text-end mb-3 container">
      <button class="btn btn-danger" @click="exportToPDF">Export PDF</button>
    </div>

    <!-- Konten yang akan diexport ke PDF -->
    <div id="laporan-pdf" class="container py-4">
      <!-- Row: Statistik Ringkas -->
      <div class="row mb-4">
        <!-- Omzet Hari Ini -->
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card text-white bg-primary shadow">
            <div class="card-body">
              <div class="card-title d-flex align-items-center justify-content-between">
                <h5>Omzet Hari Ini</h5>
                <i class="fas fa-shopping-cart" style="font-size: 5rem; color: #FFFFFF;"></i>
              </div>
              <p class="card-text fs-4 fw-bold">{{ formatRupiah(totalOmzet) }}</p>
            </div>
          </div>
        </div>
        <!-- Omzet Bulanan -->
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card text-white bg-warning shadow">
            <div class="card-body">
              <div class="card-title d-flex align-items-center justify-content-between">
                <h5>Total Omzet Perbulan</h5>
                <i class="fa-solid fa-coins" style="font-size: 5rem; color: #FFFFFF;"></i>
              </div>
              <p class="card-text fs-4 fw-bold">
                {{ formatRupiah(totalOmzetBulanan) }}
              </p>
            </div>
          </div>
        </div>


        <!-- Jumlah Transaksi -->
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card text-white bg-success shadow">
            <div class="card-body">
              <div class="card-title d-flex align-items-center justify-content-between">
                <h5>Jumlah Transaksi</h5>
                <i class="fas fa-users" style="font-size: 5rem; color: #FFFFFF;"></i>
              </div>
              <p class="card-text fs-4 fw-bold">{{ jumlahTransaksi }}</p>
            </div>
          </div>
        </div>

        <!-- Jam -->
        <!-- <div class="col-md-6 col-lg-4 mb-3">
          <div class="card text-white bg-warning shadow">
            <div class="card-body">
              <div class="card-title d-flex align-items-center justify-content-between">
                <h5>Jam</h5>
                <i class="fas fa-clock" style="font-size: 5rem; color: #FFFFFF;"></i>
              </div>
              <p class="card-text fs-4 fw-bold">{{ currentTime }}</p>
            </div>
          </div>
        </div>
      </div> -->

      <!-- Filter Bulan dan Tahun -->
      <div class="row mb-3">
        <div class="col-md-3">
          <select v-model="selectedBulan" class="form-control" @change="loadGrafik">
            <option v-for="bulan in bulanList" :key="bulan.value" :value="bulan.value">
              {{ bulan.label }}
            </option>
          </select>
        </div>
        <div class="col-md-3">
          <select v-model="selectedTahun" class="form-control" @change="loadGrafik">
            <option v-for="tahun in tahunList" :key="tahun" :value="tahun">{{ tahun }}</option>
          </select>
        </div>
      </div>

      <!-- Grafik Omzet (Bar Chart) -->
      <div class="row gap-12">
        <div class="card mb-4 shadow col-8">
          <div class="card-body">
            <h5 class="card-title text-center mb-3">📊 Pendapatan Per Hari Dalam Sebulan</h5>
            <div id="omzet-chart" class="transition-chart">
              <apexchart
                type="bar"
                height="300"
                :options="barChartOptions"
                :series="barSeries"
              />  
            </div>
          </div>
        </div>
  
        <!-- Grafik Metode Pembayaran -->
        <div class="card shadow col-3">
          <div class="card-body">
            <h5 class="card-title text-center mb-3">💳 Metode Pembayaran</h5>
            <apexchart
              type="donut"
              height="250"
              :options="metodeChartOptions"
              :series="metodeSeries"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ApexCharts from 'vue3-apexcharts'
import axios from 'axios'
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'

export default {
  components: {
    apexchart: ApexCharts,
  },
  data() {
    return {
      totalOmzet: 0,
      totalOmzetBulanan: 0,
      jumlahTransaksi: 0,

      selectedBulan: new Date().getMonth() + 1,
      selectedTahun: new Date().getFullYear(),

      bulanList: [
        { label: 'Januari', value: 1 },
        { label: 'Februari', value: 2 },
        { label: 'Maret', value: 3 },
        { label: 'April', value: 4 },
        { label: 'Mei', value: 5 },
        { label: 'Juni', value: 6 },
        { label: 'Juli', value: 7 },
        { label: 'Agustus', value: 8 },
        { label: 'September', value: 9 },
        { label: 'Oktober', value: 10 },
        { label: 'November', value: 11 },
        { label: 'Desember', value: 12 },
      ],
      tahunList: [2025, 2026, 2027, 2028],

      barChartOptions: {
        chart: {
          id: 'omzet-chart',
          toolbar: { show: false },
          animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            animateGradually: { enabled: true, delay: 150 },
            dynamicAnimation: { enabled: true, speed: 350 },
          },
        },
        legend: { show: false },
        dataLabels: {
          enabled: true,
          style: { fontSize: '10px', fontWeight: 'bold' },
          formatter: (val) => `Rp${val.toLocaleString('id-ID')}`,
        },
        colors: ['#0d6efd'],
        xaxis: {
          categories: [],
          labels: {
            rotate: -45,
            style: {
              fontSize: '13px',
              fontWeight: 600,
              colors: '#343a40',
            },
          },
        },
        yaxis: {
          labels: {
            formatter: (val) => `Rp${val.toLocaleString('id-ID')}`,
            style: {
              fontSize: '12px',
              fontWeight: 500,
              colors: '#495057',
            },
          },
          title: {
            text: 'Total Omzet',
            style: { fontWeight: 600 },
          },
        },
        tooltip: {
          custom: ({ series, seriesIndex, dataPointIndex, w }) => {
            const rawDate = w.globals.labels[dataPointIndex]
            const value = series[seriesIndex][dataPointIndex]
            const dateObj = new Date(rawDate)
            const hari = dateObj.toLocaleDateString('id-ID', { weekday: 'long' })
            const tanggalFormatted = dateObj.toLocaleDateString('id-ID')
            return `
              <div style="padding: 8px;">
                <text>Hari:</text> ${hari}<br/>
                <text>Tanggal:</text> ${tanggalFormatted}<br/>
                <text>Omzet:</text> Rp${value.toLocaleString('id-ID')}
              </div>
            `
          },
        },
        plotOptions: {
          bar: {
            distributed: true,
            borderRadius: 5,
            endingShape: 'rounded',
          },
        },
        grid: {
          borderColor: '#dee2e6',
          strokeDashArray: 8,
          row: {
            colors: ['#f8f9fa', 'transparent'],
            opacity: 2,
          },
        },
      },
      barSeries: [{ name: 'Omzet', data: [] }],
      metodeChartOptions: {
        chart: { id: 'metode-chart' },
        labels: ['Cash', 'Qris'],
        colors: ['#198754', '#6610f2'],
        legend: { position: 'bottom' },
      },
      metodeSeries: [],
    }
  },
  mounted() {
    this.loadHarini()
    this.loadGrafik()
    this.loadMetodePembayaran()
    this.initScrollObserver()
  },
  methods: {
    formatRupiah(angka) {
      if (typeof angka !== 'number') return angka
      return `Rp${angka.toLocaleString('id-ID')}`
    },

    async loadHarini() {
      try {
        const res = await axios.get('/statistik/harian')
        this.totalOmzet = Number(res.data.total_omzet)
        this.jumlahTransaksi = res.data.jumlah_transaksi
      } catch (err) {
        console.error('Gagal load data harian:', err)
      }
    },

    async loadGrafik() {
      try {
        const res = await axios.get('/statistik/grafik', {
          params: {
            bulan: this.selectedBulan,
            tahun: this.selectedTahun,
          },
        })
        const data = res.data

        const tanggal = data.map((item) => item.tanggal)
        const omzet = data.map((item) => item.total)

        this.barChartOptions = {
          ...this.barChartOptions,
          xaxis: {
            ...this.barChartOptions.xaxis,
            categories: tanggal,
           
           
            labels: {
              ...this.barChartOptions.xaxis.labels,
                rotate: '-90',
                style: {
                  fontSize: '10px',
                },

               formatter: (val) => {
                const date = new Date(val)
                if (isNaN(date)) return val
                const dd = String(date.getDate()).padStart(2, '0')
                const mm = String(date.getMonth() + 1).padStart(2, '0')
                const yyyy = date.getFullYear()
                return `${dd}/${mm}/${yyyy}`
              },
            },
          },
        }

        this.barSeries[0].data = omzet
        this.totalOmzetBulanan = omzet.reduce((acc, curr) => acc + Number(curr), 0)
      } catch (err) {
        console.error('Gagal load grafik:', err)
      }
    },

    async loadMetodePembayaran() {
      try {
        const res = await axios.get('/statistik/metode')
        const data = res.data
        this.metodeChartOptions.labels = data.map((item) => item.metode)
        this.metodeSeries = data.map((item) => item.jumlah)
      } catch (err) {
        console.error('Gagal load metode pembayaran:', err)
      }
    },

    initScrollObserver() {
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            const target = entry.target
            if (entry.isIntersecting) {
              target.classList.add('scale-up')
              target.classList.remove('scale-down')
            } else {
              target.classList.remove('scale-up')
              target.classList.add('scale-down')
            }
          })
        },
        { threshold: 0.4 }
      )
      const chart = document.querySelector('#omzet-chart')
      if (chart) observer.observe(chart)
    },

    async exportToPDF() {
      const laporanEl = document.querySelector('#laporan-pdf')
      if (!laporanEl) return alert('Elemen laporan tidak ditemukan.')

      try {
        const canvas = await html2canvas(laporanEl, { scale: 2 })
        const imgData = canvas.toDataURL('image/png')
        const pdf = new jsPDF('p', 'mm', 'a4')

        const pdfWidth = pdf.internal.pageSize.getWidth()
        const imgProps = pdf.getImageProperties(imgData)
        const imgRatio = imgProps.width / imgProps.height
        const pdfHeight = pdfWidth / imgRatio

        pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight)
        pdf.save(`laporan-omzet-${this.selectedBulan}-${this.selectedTahun}.pdf`)
      } catch (err) {
        console.error('Export PDF gagal:', err)
      }
    },
  },
}
</script>




<style scoped>
#omzet-chart .apexcharts-bar-series path {
  transition: transform 0.2s ease;
}

#omzet-chart .apexcharts-bar-series:hover path {
  transform: scale(1.05);
  cursor: pointer;
}

@keyframes scaleUp {
  0% {
    transform: scale(0.95);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes scaleDown {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(0.95);
    opacity: 0.5;
  }
}

.scale-up {
  animation: scaleUp 0.5s ease forwards;
}

.scale-down {
  animation: scaleDown 0.5s ease forwards;
}

.transition-chart {
  transition: transform 0.5s ease, opacity 0.5s ease;
}
</style>
