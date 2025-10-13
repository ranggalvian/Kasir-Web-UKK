<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import axios from '@/libs/axios';
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Produk } from "@/types";
import ApiService from "@/core/services/ApiService";

const column = createColumnHelper<Produk>();
const paginateRef = ref<any>(null);
const selected = ref<number>(0);
const openForm = ref<boolean>(false);

const { delete: deleteProduk } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

// Konfigurasi kolom tabel untuk daftar produk
const columns = [
    column.accessor("no", {
        header: "No",
        cell: (cell) => cell.row.index + 1,
    }),
    column.accessor("photo", {
        header: "Menu Foto",
         cell: (cell) =>
            h("img",{
                    src:`/storage/${cell.getValue()}`,
                    height: "100",
                    alt: "Menu Photo"
                }) 
    }),
    column.accessor("nama_produk", {
        header: "Menu",
    }),
    // // column.accessor("cup_size_id", {
    // //     header: "Size Cup",
    // // }),
    // // column.accessor("Sugar_level_id", {
    // //     header: "Sugar Level",
    // }),
    column.accessor("nama_kategori", {
        header: "Kategori",
    }),
    column.accessor("harga", {
        header: "Harga",
    }),
    column.accessor("id_produk", {
  header: "Ketersediaan",
  cell: (cell) =>{
      const item = cell.row.original;
      ;
    
      return h("div", { class: "d-flex gap-2" }, [
        h(
          "button",
          {
            class: `btn btn-sm ${
              item.ketersediaan === "Tidak Tersedia" ? "btn-danger" : "btn-success"
            }`,
            onClick: async () => {
            const newStatus = item.ketersediaan === "Tersedia" ? "Tidak Tersedia" : "Tersedia"
              item.ketersediaan = newStatus;
  
              try {
                await ApiService.put(`/master/produk/switch/${cell.getValue()}`, {
                  ketersediaan: newStatus,
                });
                refresh();
              } catch (error) {
                console.error("Gagal update status:", error);
                // Rollback status jika gagal
                
              }
            },
          },
          h("h", item.ketersediaan) 
        ),
      ]);
  },


    }),
    column.accessor("id_produk", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            console.log(selected.value)
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () =>
                            deleteProduk(`/master/produk/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
    if (!val) {
        selected.value = 0;
    }
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Menu Makanan & Minuman</h2>
            <button
                type="button"
                class="btn btn-sm btn-primary ms-auto"
                v-if="!openForm"
                @click="openForm = true"
            >
                Tambah Menu
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-produks"
                url="/master/produk"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
