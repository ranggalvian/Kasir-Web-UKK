<?php
namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    public function get(){
        return response()->json(
            Kategori::all()
        );
    }

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no = 0 +' . $page * $per);
        $data = Kategori:: // Ambil data kategori beserta produk
            when($request->search, function (Builder $query, string $search) {
                $query->where('nama_kategori', 'like', "%$search%");
            })
            ->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        // Simpan kategori
        $kategori = Kategori::create([
            'nama_kategori' => $request->nama_kategori
        ]);


        return response()->json(['success' => true, 'kategori' => $kategori->load('produk')]);
    }

    public function show(Kategori $kategori): JsonResponse
    {
        return response()->json([
            'kategori' => $kategori->nama_kategori // Tambahkan kategori dalam response
        ]);
    }

    public function update(KategoriRequest $request, Kategori $kategori)
    {
        $kategori->update($request->validated());

        return response()->json([
            'success' => true,
            // 'produk' => $kategori->load('nama_kategori') // Tambahkan kategori dalam response
        ]);
    }

    public function destroy(Kategori $kategori): JsonResponse
    {
        $kategori->delete();
        return response()->json(['success' => true, 'message' => 'Produk berhasil dihapus']);
    }
}