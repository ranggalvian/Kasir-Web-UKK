<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdukRequest;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProdukController extends Controller
{
    // Untuk halaman pembelian
    public function get(): JsonResponse
    {
        $produk = Produk::with(['kategori',])->get(); // 'cupSizes', 'sugarLevels'
        return response()->json($produk);
    }

    public function index(Request $request): JsonResponse
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
    
        DB::statement('set @no=0+' . $page * $per);
    
        $data = Produk::join('kategori', 'produk.id_kategori', '=', 'kategori.id_kategori')
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('produk.nama_produk', 'like', "%$search%")
                    ->orWhere('kategori.nama_kategori', 'like', "%$search%");
            })
            ->select('produk.*', 'kategori.nama_kategori AS nama_kategori')
            ->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);
    
        return response()->json($data);
    }

    // Tombol on/off ketersediaan
    public function Ketersediaan($id_produk, Produk $produk)
    {
        $status = Produk::where('id_produk', $id_produk)->first('ketersediaan');
        $status = $status->ketersediaan === 'Tersedia' ? 'Tidak Tersedia' : 'Tersedia';

        $produk->where('id_produk', $id_produk)->update(['ketersediaan' => $status]);

        return response()->json(['success' => true]);
    }  

    public function store(ProdukRequest $request): JsonResponse
    {  
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photo', 'public');
        }

        $produk = Produk::create($validated);

        // Simpan relasi ke tabel pivot
        // if ($request->has('cup_sizes')) {
        //     $produk->cupSizes()->sync($request->cup_sizes);
        // }
        // if ($request->has('sugar_levels')) {
        //     $produk->sugarLevels()->sync($request->sugar_levels);
        // }

        return response()->json([
            'success' => true,
            'produk' => $produk->load(['kategori',])  //'cupSizes', 'sugarLevels'
        ]);
    }

    public function show(Produk $produk): JsonResponse
    {
        return response()->json([
            'produk' => $produk->load(['kategori',]) // 'cupSizes', 'sugarLevels'
        ]);
    }

    public function update(ProdukRequest $request, Produk $produk): JsonResponse
    {
        Log::info($produk->id_produk);

        if ($request->hasFile('photo')) {
            $validated = $request->validated();
            $validated['photo'] = $request->file('photo')->store('photo', 'public');
            $produk->update($validated);
        } else {
            $produk->update($request->validated());
        }

        // Update relasi di tabel pivot
        // if ($request->has('cup_sizes')) {
        //     $produk->cupSizes()->sync($request->cup_sizes);
        // }
        // if ($request->has('sugar_levels')) {
        //     $produk->sugarLevels()->sync($request->sugar_levels);
        // }

        return response()->json([
            'success' => true,
            'produk' => $produk->load(['kategori',]) // 'cupSizes', 'sugarLevels'
        ]);
    }

    public function destroy(Produk $produk): JsonResponse
    {
        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
