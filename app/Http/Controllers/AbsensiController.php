<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'status' => 'required|in:masuk,pulang',
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
    ]);

    // Titik lokasi toko
    $lokasiToko = [
        'lat' => -7.265757,
        'lng' => 112.734146
    ];

    $jarak = $this->hitungJarak($lokasiToko['lat'], $lokasiToko['lng'], $request->lat, $request->lng);

    if ($jarak > 100) {
        return response()->json(['message' => 'Anda di luar area lokasi toko.']);
    }

    // Cek apakah user sudah absen status yang sama hari ini
    $sudahAbsen = Absensi::where('user_id', auth()->id())
        ->where('status', $request->status)
        ->whereDate('created_at', now()->toDateString())
        ->exists();

    if ($sudahAbsen) {
        return response()->json([
            'message' => 'Anda sudah absen ' . $request->status . ' hari ini.'
        ]);
    }

    // Simpan absensi
    Absensi::create([
        'user_id' => auth()->id(),
        'status' => $request->status,
        'lat' => $request->lat,
        'lng' => $request->lng,
        'jarak' => $jarak,
    ]);

    return response()->json(['message' => 'Absensi berhasil.']);
}

    private function hitungJarak($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
}