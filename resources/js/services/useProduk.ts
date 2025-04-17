import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useProduk(options = {}) {
    return useQuery({
        queryKey: ["Produk"],
        queryFn: async () =>
            await axios.get("/info_produk").then((res: any) => res.data.data),
        ...options,
    });
}
