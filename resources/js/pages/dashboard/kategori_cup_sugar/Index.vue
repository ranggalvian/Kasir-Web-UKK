<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import FormCupSize from "./FormCupSize.vue"; // Form khusus cup size
import { createColumnHelper } from "@tanstack/vue-table";
import type { CupSize } from "@/types/cupSize"; // buat type baru
import axios from "@/libs/axios";

const column = createColumnHelper<CupSize>();
const paginateRef = ref<any>(null);
const selected = ref<number | null>(null);
const openForm = ref<boolean>(false);
const cupSizeList = ref<CupSize[]>([]);

const { delete: deleteCupSize } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "No",
        cell: (cell) => cell.row.index + 1,
    }),
    column.accessor("cup-size", {
        header: "Cup Size",
    }),
    column.accessor("sugar-level", {
        header: "Sugar Level",
    }),
    column.accessor("id", {
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
                        onClick: () => deleteCupSize(`/master/cup-size/${cell.getValue()}`),
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
    <FormCupSize 
        :selected="selected" 
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Cup Size</h2>
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
                id="table-cup-size"
                url="/master/cup-size"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
