export const formatRupiah = (value: number | string): string => {
    return new Intl.NumberFormat("id-ID").format(Number(value));
  };