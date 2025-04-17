import { useQuery } from "@tanstack/vue-query";
import axios from "@/libs/axios";

export function useKategori(options = {}) {
    return useQuery({
        queryKey: ["kategori"],
        queryFn: async () =>
            await axios.get("/master/kategori").then((res: any) => res.data),
        ...options,
    });
}
