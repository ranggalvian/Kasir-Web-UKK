<script setup lang="ts">
import { ref, watch, defineProps, defineEmits, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Kategori } from "@/types";
import { isRowSelected } from "@tanstack/vue-table";

const props = defineProps<{ selected: number | null }>();
const emit = defineEmits(["close", "refresh"]);

const produk = ref({
    nama_produk: "",
    id_kategori: "",
    harga: 0, // harga asli
});
const kategori = ref<Kategori[]>([]);
const loading = ref<boolean>(false);
const formattedHarga = ref("");

// Format tampilan harga saat user mengetik
function onHargaInput(e: Event) {
    const input = e.target as HTMLInputElement;
    const raw = input.value.replace(/[^\d]/g, ""); // hanya angka
    produk.value.harga = parseInt(raw || "0");
    formattedHarga.value = formatRupiah(raw);
}

// Format angka 
function formatRupiah(angka: string | number): string {
    const numberString = angka.toString().replace(/[^,\d]/g, "");
    const split = numberString.split(",");
    const sisa = split[0].length % 3;
    let rupiah = split[0].substring(0, sisa);
    const ribuan = split[0].substring(sisa).match(/\d{3}/g);
    if (ribuan) {
        rupiah += (sisa ? "." : "") + ribuan.join(".");
    }
    return rupiah ? "Rp " + rupiah : "";
}

// Ambil kategori dari API
const fetchKategori = async () => {
    try {
        const response = await axios.get("/master/kategori");
        kategori.value = response.data;
    } catch (error) {
        toast.error("Gagal mengambil data kategori");
    }
};

const fetchProduk = async (id: number) => {
  try {
    const response = await axios.get(`/master/produk/${id}`);
    const data = response.data;

    // Map hanya field yang dibutuhkan
    produk.value = {
      nama_produk: data.produk.nama_produk || "",
      id_kategori: data.produk.id_kategori || "",
      harga: data.produk.harga || null,
    };
    formattedHarga.value = formatRupiah(data.produk.harga || 0);
  } catch (error) {
    toast.error("Gagal mengambil data produk");
  }
};


// Simpan produk
const saveProduk = async () => {
    loading.value = true;
    try {
        if (props.selected) {
            await axios.put(`/master/produk/${props.selected}`, produk.value);
            toast.success("Produk berhasil diperbarui!");
        } else {
            await axios.post("/master/produk/store", produk.value);
            toast.success("Produk berhasil ditambahkan!");
        }
        emit("refresh");
        emit("close");
    } catch (error) {
        toast.error("Gagal menyimpan produk");
    } finally {
        loading.value = false;
    }
};

// Reset form saat modal dibuka/tutup
watch(() => props.selected, (newVal) => {
    console.log("selected berubah:", newVal);
    if (newVal) {
        fetchProduk(newVal);
    } else {
        produk.value = { nama_produk: "", id_kategori: "", harga: 0 };
        formattedHarga.value = "";
    }
});

onMounted(() => {
    fetchKategori();
    if(props.selected){
        fetchProduk(props.selected)
    }
});
</script>

<template>
    <div class="modal show d-block">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ selected ? "Edit Produk" : "Tambah Produk" }}
                    </h5>
                    <button type="button" class="btn-close" @click="emit('close')"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveProduk">
                        <!-- Input Nama Produk -->
                        <div class="mb-3">
                            <label class="form-label">Nama Menu</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="produk.nama_produk"
                                required
                            />
                        </div>

                        <!-- Dropdown Kategori -->
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" v-model="produk.id_kategori" required>
                                <option value="" disabled>Pilih Kategori</option>
                                <option
                                    v-for="katego in kategori"
                                    :key="katego.id_kategori"
                                    :value="katego.id_kategori"
                                >
                                    {{ katego.nama_kategori }}
                                </option>
                            </select>
                        </div>

                        <!-- Input Harga (format Rp) -->
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input
                                type="text"
                                class="form-control"
                                :value="formattedHarga"
                                @input="onHargaInput"
                                required
                            />
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="emit('close')">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                {{ loading ? "Menyimpan..." : "Simpan" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
