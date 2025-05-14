<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatPemesanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatistikController extends Controller
{
    public function harian()
    {
        $today = Carbon::today();

        $totalOmzet = RiwayatPemesanan::whereDate('created_at', $today)->sum('total_harga');
        $jumlahTransaksi = RiwayatPemesanan::whereDate('created_at', $today)->count();

        return response()->json([
            'total_omzet' => $totalOmzet,
            'jumlah_transaksi' => $jumlahTransaksi,
        ]);
    }

    public function grafik(Request $request)
    {
        // Ambil bulan dan tahun dari query params, default ke bulan & tahun sekarang
        $bulan = $request->query('bulan', Carbon::now()->month);
        $tahun = $request->query('tahun', Carbon::now()->year);

        $data = RiwayatPemesanan::select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(total_harga) as total')
            )
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return response()->json($data);
    }

    public function metodePembayaran()
    {
        $data = RiwayatPemesanan::select(
                'metode',
                DB::raw('COUNT(*) as jumlah')
            )
            ->groupBy('metode')
            ->get();

        return response()->json($data);
    }
}
