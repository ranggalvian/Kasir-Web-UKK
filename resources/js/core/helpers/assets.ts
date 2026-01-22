import { illustrationsSet } from "@/layouts/default-layout/config/helper";
import { useThemeStore } from "@/stores/theme";

const BASE_URL = import.meta.env.APP_URL || "http://127.0.0.1:8000";

/**
 * Get illustration path from assets based on theme mode
 */
export const getIllustrationsPath = (illustrationName: string): string => {
  // Ambil posisi titik terakhir (untuk ekstensi)
  const lastDotIndex = illustrationName.lastIndexOf(".");
  const hasExtension = lastDotIndex !== -1;

  const name = hasExtension
    ? illustrationName.slice(0, lastDotIndex)
    : illustrationName;

  const ext = hasExtension
    ? illustrationName.slice(lastDotIndex)
    : "";

  // Tambahkan suffix -dark jika tema dark
  const theme = useThemeStore().mode === "dark" ? `${name}-dark` : name;

  return `${BASE_URL}media/illustrations/${illustrationsSet.value}/${theme}${ext}`;
};

/**
 * Get asset path from public folder
 */
export const getAssetPath = (path: string): string => {
    console.log("Base URL:", BASE_URL);
    console.log(`${BASE_URL}${path.startsWith("/") ? path : "/" + path}`);
    return `${BASE_URL}${path.startsWith("/") ? path : "/" + path}`;
};

/**
 * Get storage asset path
*/
export const getStoragePath = (path: string): string => {
    console.log("Base URL:", BASE_URL);
    console.log(`${BASE_URL}/storage/${path}`);
  return `${BASE_URL}/storage/${path}`;
};
