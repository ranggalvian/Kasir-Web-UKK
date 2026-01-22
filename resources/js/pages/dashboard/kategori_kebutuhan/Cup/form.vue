<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import { useCrud } from "@/libs/useCrud";


const props = defineProps({
    selected: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(["close", "refresh"]);

const form = ref({
    name: "",
    extra_price: "",
});

const isEdit = ref(false);

// Hook API
const { store, loading: storeLoading } = useStore({
    onSuccess: () => {
        emit("refresh");
        emit("close");
    },
});

const { update, loading: updateLoading } = useUpdate({
    onSuccess: () => {
        emit("refresh");
        emit("close");
    },
});

const { show, loading: showLoading } = useShow();

// Load data jika edit
const loadData = async () => {
    if (props.selected) {
        isEdit.value = true;
        const res = await show(`/cup-sizes/${props.selected}`);

        form.value.name = res.data.name;
        form.value.extra_price = res.data.extra_price;
    } else {
        isEdit.value = false;
        form.value = { name: "", extra_price: "" };
    }
};

onMounted(loadData);

watch(
    () => props.selected,
    () => loadData()
);

// Submit
const save = () => {
    if (isEdit.value) {
        update(`/cup-sizes/${props.selected}`, form.value);
    } else {
        store("/cup-sizes", form.value);
    }
};
</script>

<template>
    <div class="card mb-5 mt-3">
        <div class="card-header align-items-center">
            <h3 class="mb-0">
                {{ isEdit ? "Edit Cup Size" : "Tambah Cup Size" }}
            </h3>
            <button class="btn btn-sm btn-light ms-auto" @click="$emit('close')">
                <i class="la la-arrow-left"></i> Back
            </button>
        </div>

        <div class="card-body">
            <form @submit.prevent="save">
                <div class="mb-3">
                    <label class="form-label">Nama Cup Size</label>
                    <input
                        type="text"
                        v-model="form.name"
                        class="form-control"
                        placeholder="Contoh: Large, Medium"
                    />
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Tambahan (Rp)</label>
                    <input
                        type="number"
                        v-model="form.extra_price"
                        class="form-control"
                        min="0"
                        placeholder="Contoh: 2000"
                    />
                </div>

                <div class="d-flex justify-content-end">
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="storeLoading || updateLoading"
                    >
                        {{ isEdit ? "Update" : "Simpan" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
