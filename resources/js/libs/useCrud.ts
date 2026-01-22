import { ref } from "vue";
import axios from "@/libs/axios";

export function useCrud(options: any = {}) {
    const loading = ref(false);

    // GET detail
    const show = async (url: string) => {
        loading.value = true;

        try {
            const res = await axios.get(url);
            return res;
        } catch (err) {
            console.error(err);
            options.onError && options.onError(err);
        } finally {
            loading.value = false;
        }
    };

    // POST create
    const store = async (url: string, data: any) => {
        loading.value = true;

        try {
            const res = await axios.post(url, data);
            options.onSuccess && options.onSuccess(res.data);
        } catch (err) {
            console.error(err);
            options.onError && options.onError(err);
        } finally {
            loading.value = false;
        }
    };

    // PUT update
    const update = async (url: string, data: any) => {
        loading.value = true;

        try {
            const res = await axios.put(url, data);
            options.onSuccess && options.onSuccess(res.data);
        } catch (err) {
            console.error(err);
            options.onError && options.onError(err);
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        show,
        store,
        update
    };
}
