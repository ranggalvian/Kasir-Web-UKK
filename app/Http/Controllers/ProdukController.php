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
    // Digunakan untuk halaman pembelian (ambil semua produk dengan kategori)
    public function get(): JsonResponse
    {
        $produk = Produk::with('kategori')->get();

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

    public function store(ProdukRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $produk = Produk::create($validated);

        return response()->json([
            'success' => true,
            'produk' => $produk->load('kategori')
        ]);
    }

    public function show(Produk $produk): JsonResponse
    {
        return response()->json([
            'produk' => $produk->load('kategori')
        ]);
    }

    public function update(ProdukRequest $request, Produk $produk): JsonResponse
    {
        Log::info($produk->id_produk);
        $produk->update($request->validated());

        if($produk){
            return response()->json([
                'success' => true,
                'produk' => $produk->load('kategori')
            ]);
        }
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
