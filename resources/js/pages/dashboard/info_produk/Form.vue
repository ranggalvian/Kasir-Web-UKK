<script setup lang="ts">
import { ref, watch, defineProps, defineEmits, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Kategori } from "@/types";

const props = defineProps<{ selected: number | null }>();
const emit = defineEmits(["close", "refresh"]);

// form produk

const produk = ref({
    nama_produk: "",
    id_kategori: "",
    harga: "",
});
const kategori = ref<Kategori>({} as Kategori);
const loading = ref<boolean>(false);

// Ambil daftar kategori dari API
const fetchKategori = async () => {
    try {
        const response = await axios.get("/master/kategori");
        kategori.value = response.data;
    } catch (error) {
        toast.error("Gagal mengambil data kategori");
    }
};

// Ambil detail produk jika sedang edit
const fetchProduk = async (id: number) => {
    try {
        const response = await axios.get(`/master/produk/${id}`);
        produk.value = response.data;
    } catch (error) {
        toast.error("Gagal mengambil data produk");
    }
};

// Simpan Produk (Tambah / Edit)
const saveProduk = async () => {
    console.log(produk.value);  
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

// Reset form jika ditutup
watch(() => props.selected, (newVal) => {
    if (newVal) {
        fetchProduk(newVal);
    } else {
        produk.value = { nama_produk: "", id_kategori: "", harga: "" };
    }
});

// Ambil kategori saat komponen dimuat
onMounted(() => {
    fetchKategori();
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
                                <option v-for="katego in kategori" :key="katego.id_kategori" :value="katego.id_kategori">
                                    {{ katego.nama_kategori}}
                                </option>
                            </select>
                        </div>

                        <!-- Input Harga -->
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input
                                type="number"
                                class="form-control"
                                v-model="produk.harga"
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
