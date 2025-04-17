<script setup lang="ts">
import { h, ref, watch, onMounted } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Formkategori.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Kategori } from "@/types/kategori";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";

const column = createColumnHelper<Kategori>();
const paginateRef = ref<any>(null);
const selected = ref<number | null>(null);
const openForm = ref<boolean>(false);
const kategoriList = ref<Kategori[]>([]);

const { delete: deleteKategori } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "No",
        cell: (cell) => cell.row.index + 1,
    }),
    column.accessor("nama_kategori", {
        header: "Kategori Menu",
    }),
    column.accessor("id_kategori", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () => deleteKategori(`/master/kategori/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
    if (!val) {
        selected.value = null;
    }
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />
       


    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Kategori Menu</h2>
            <button type="button"
                class="btn btn-sm btn-primary ms-auto"
                v-if="!openForm"
                @click="openForm = true"
            >
                Tambah  
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-kategori"
                url="/master/kategori"
                :columns="columns"
            ></paginate>
        </div>
    </div>
    
</template>    
