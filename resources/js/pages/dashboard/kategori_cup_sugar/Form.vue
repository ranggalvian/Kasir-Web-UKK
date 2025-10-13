<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";

// Props: selected = id data yang di-edit, type = kategori | cup_size | sugar_level
const props = defineProps({
  selected: { type: String, default: null },
  type: { type: String, default: "kategori" },
});

const emit = defineEmits(["close", "refresh"]);

const formRef = ref();
const formModel = ref<{ nama: string }>({ nama: "" });

// Validasi form
const formSchema = Yup.object().shape({
  nama: Yup.string().required("Nama harus diisi"),
});

// Ambil data untuk edit
function getEdit() {
  block(document.getElementById("form-master"));
  ApiService.get(`master/${props.type}`, props.selected)
    .then(({ data }) => {
      // backend harus kirim { nama: "..." }
      formModel.value.nama = data.nama;
    })
    .catch((err: any) => {
      toast.error(err.response?.data?.message || err.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-master"));
    });
}

// Submit form
function submit() {
  const formData = new FormData();
  formData.append("nama", formModel.value.nama);

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-master"));
  axios({
    method: "post",
    url: props.selected
      ? `/master/${props.type}/${props.selected}`
      : `/master/${props.type}/store`,
    data: formData,
    headers: { "Content-Type": "multipart/form-data" },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error(err.response?.data?.message || err.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-master"));
    });
}

onMounted(() => {
  if (props.selected) getEdit();
});

watch(
  () => props.selected,
  () => {
    if (props.selected) getEdit();
  }
);
</script>

<template>
  <VForm
    class="form card mb-10"
    @submit="submit"
    :validation-schema="formSchema"
    id="form-master"
    ref="formRef"
  >
    <div class="card-header align-items-center">
      <h2 class="mb-0">
        {{ selected ? "Edit" : "Tambah" }}
        {{ props.type === "kategori"
          ? "Kategori"
          : props.type === "cup_size"
          ? "Cup Size"
          : "Sugar Level" }}
      </h2>
      <button
        type="button"
        class="btn btn-sm btn-light-danger ms-auto"
        @click="emit('close')"
      >
        Batal
        <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">
              Nama
              {{
                props.type === "kategori"
                  ? "Kategori"
                  : props.type === "cup_size"
                  ? "Cup Size"
                  : "Sugar Level"
              }}
            </label>
            <Field
              class="form-control form-control-lg form-control-solid"
              type="text"
              name="nama"
              autocomplete="off"
              v-model="formModel.nama"
              :placeholder="`Masukkan nama ${
                props.type === 'kategori'
                  ? 'kategori'
                  : props.type === 'cup_size'
                  ? 'cup size'
                  : 'sugar level'
              }`"
            />
            <div class="fv-plugins-message-container">
              <div class="fv-help-block">
                <ErrorMessage name="nama" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">
        Simpan
      </button>
    </div>
  </VForm>
</template>
