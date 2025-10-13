<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Kategori } from "@/types";

const props = defineProps<{ selected: number | null }>();
const emit = defineEmits(["close", "refresh"]);

const produk = ref({
  nama_produk: "",
  id_kategori: "",
  harga: 0,
  photo: null as File | string | null, // File saat upload, string saat edit (path)
});

const kategori = ref<Kategori[]>([]);
const loading = ref(false);
const formattedHarga = ref("");
const photoPreview = ref<string | null>(null); // url preview untuk file atau path

// Format harga saat input
function onHargaInput(e: Event) {
  const input = e.target as HTMLInputElement;
  const raw = input.value.replace(/[^\d]/g, "");
  produk.value.harga = parseInt(raw || "0");
  formattedHarga.value = formatRupiah(raw);
}

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

// Ambil kategori
const fetchKategori = async () => {
  try {
    const response = await axios.get("/master/kategori");
    // Sesuaikan jika API mengembalikan {status:true, data: [...]}
    kategori.value = Array.isArray(response.data) ? response.data : (response.data.data ?? []);
  } catch (error) {
    toast.error("Gagal mengambil data kategori");
    console.error("fetchKategori error:", error);
  }
};

// Ambil produk ketika edit
const fetchProduk = async (id: number) => {
  try {
    const response = await axios.get(`/master/produk/${id}`);
    const data = response.data.produk ?? response.data.data ?? response.data;
    produk.value.nama_produk = data.nama_produk ?? "";
    produk.value.id_kategori = data.id_kategori ?? "";
    produk.value.harga = Number(data.harga ?? 0);
    formattedHarga.value = formatRupiah(produk.value.harga);
    // jika ada path foto dari backend, set preview
    if (data.photo) {
      produk.value.photo = data.photo; // path string
      // pastikan path sesuai (misal 'produk/abc.jpg'), kita tampilkan sebagai /storage/...
      photoPreview.value = `/storage/${data.photo}`;
    } else {
      produk.value.photo = null;
      photoPreview.value = null;
    }
  } catch (error) {
    toast.error("Gagal mengambil data produk");
    console.error("fetchProduk error:", error);
  }
};

// File change handler
function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    produk.value.photo = file;
    // create preview URL (browser)
    if (photoPreview.value) {
      // revoke existing if created by createObjectURL
      try { URL.revokeObjectURL(photoPreview.value); } catch {}
    }
    photoPreview.value = URL.createObjectURL(file);
  }
}

// Revoke preview on unmount
onUnmounted(() => {
  if (photoPreview.value && (produk.value.photo instanceof File)) {
    try { URL.revokeObjectURL(photoPreview.value); } catch {}
  }
});

// Save produk (create / update) menggunakan FormData
const saveProduk = async () => {
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append("nama_produk", produk.value.nama_produk);
    formData.append("id_kategori", String(produk.value.id_kategori));
    formData.append("harga", String(produk.value.harga));

    // hanya append file jika benar2 File (bukan string path)
    if (produk.value.photo && produk.value.photo instanceof File) {
      formData.append("photo", produk.value.photo);
    }

    let res;
    if (props.selected) {
      // Laravel seringkali tidak menerima PUT multipart, gunakan POST + _method override
      formData.append("_method", "PUT");
      res = await axios.post(`/master/produk/${props.selected}`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    } else {
      res = await axios.post("/master/produk/store", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
    }

    // Debugging: lihat respon
    console.log("saveProduk response:", res.data);

    // Tampilkan pesan dari server bila ada, atau sukses default
    if (res.data && (res.data.status === false)) {
      // server validasi gagal
      toast.error(res.data.message || "Gagal menyimpan produk");
    } else {
      toast.success(props.selected ? "Produk berhasil diperbarui!" : "Produk berhasil ditambahkan!");
      emit("refresh");
      emit("close");
    }
  } catch (err: any) {
    console.error("saveProduk error:", err);
    // Tampilkan pesan validasi dari server jika ada
    if (err.response) {
      const resp = err.response;
      // kalau backend kirim errors object (Laravel validation)
      if (resp.data && resp.data.errors) {
        const messages = Object.values(resp.data.errors).flat().join("\n");
        toast.error(messages);
      } else if (resp.data && resp.data.message) {
        toast.error(resp.data.message);
      } else {
        toast.error(`Gagal menyimpan produk (status ${resp.status})`);
      }
    } else {
      toast.error("Gagal menyimpan produk (network error)");
    }
  } finally {
    loading.value = false;
  }
};

watch(() => props.selected, (newVal) => {
  if (newVal) {
    fetchProduk(newVal);
  } else {
    produk.value = { nama_produk: "", id_kategori: "", harga: 0, photo: null };
    formattedHarga.value = "";
    if (photoPreview.value) {
      try { URL.revokeObjectURL(photoPreview.value); } catch {}
      photoPreview.value = null;
    }
  }
});

onMounted(() => {
  fetchKategori();
  if (props.selected) fetchProduk(props.selected);
});
</script>

<template>
  <div class="modal show d-block">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ props.selected ? "Edit Produk" : "Tambah Produk" }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="saveProduk" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Nama Menu</label>
              <input type="text" class="form-control" v-model="produk.nama_produk" required />
            </div>

            <div class="mb-3">
              <label class="form-label">Kategori</label>
              <select class="form-control" v-model="produk.id_kategori" required>
                <option value="" disabled>Pilih Kategori</option>
                <option v-for="k in kategori" :key="k.id_kategori" :value="k.id_kategori">
                  {{ k.nama_kategori }}
                </option>
              </select>
            </div>

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

            <div class="mb-3">
              <label class="form-label">Foto Menu (opsional)</label>
              <input type="file" class="form-control" accept="image/*" @change="onFileChange" />
            </div>

            <div v-if="photoPreview" class="mb-3">
              <label class="form-label">Preview Foto</label>
              <div>
                <img :src="photoPreview" alt="Preview Foto" style="max-height:150px; border-radius:8px;" />
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="emit('close')">Batal</button>
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
