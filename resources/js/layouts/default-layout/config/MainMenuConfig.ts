import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: "Dashboard",
                name: "dashboard",
                route: "/dashboard",
                keenthemesIcon: "element-11",
            },
        ],
    },

    // WEBSITE
    {
        heading: "Website",
        route: "/dashboard/website",
        name: "website",
        pages: [
            // MASTER
            {
                sectionTitle: "Master",
                route: "/master",
                keenthemesIcon: "cube-3",
                name: "master",
                sub: [
                    {
                        sectionTitle: "User",
                        route: "/users",
                        name: "master-user",
                        sub: [
                            {
                                heading: "Role",
                                name: "master-role",
                                route: "/dashboard/master/users/roles",
                            },
                            {
                                heading: "User",
                                name: "master-user",
                                route: "/dashboard/master/users",
                            },
                        ],
                    },
                ],
            },
            // produk
            {
                sectionTitle: "Info Menu",
                route: "/info_produk",
                keenthemesIcon: "shop",
                name: "info-produk",
                sub: [
                    {
                        heading: "Menu",
                        name: "daftar-produk",
                        route: "/dashboard/info_produk/produk",
                    },
                    {
                        heading: "Kategori Menu",
                        name: "produk-kategori",
                        route: "/dashboard/info_produk/kategori",
                    },

                ],
            },

            // cup size dan sugar level
            {
                sectionTitle: "Kebutuhan Kategori",
                route: "/kategori_kebutuhan",
                keenthemesIcon: "shop",
                name: "kebutuhan-kategori",
                sub: [
                    {
                        heading: "Kategori cup size",
                        name: "daftar-cup-size",
                        route: "/dashboard/kategori_kebutuhan/cup",
                    },
                    {
                        heading: "Kategori sugar level",
                        name: "daftar-sugar-level",
                        route: "/dashboard/kategori_kebutuhan/sugar",
                    },

                ],
            },

            {
                heading: "Pembelian",
                route: "/dashboard/pembelian",
                name: "pembelian-produk",
                keenthemesIcon: "handcart"
            },

            {
                heading: "Riwayat pemesanan",
                route: "/dashboard/riwayat-pemesanan",
                name: "riwayat-pemesanan",
                keenthemesIcon: "burger-menu"

            },

            {
                heading: "Setting",
                route: "/dashboard/setting",
                name: "setting",
                keenthemesIcon: "setting-2",
            },
        ],
    },
];

export default MainMenuConfig;
