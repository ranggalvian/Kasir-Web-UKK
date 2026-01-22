<?php

namespace App\Http\Controllers;

use App\Models\CupSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CupSizeController extends Controller
{
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => CupSize::when($request->role_id, function (Builder $query, string $role_id) {
                $query->role($role_id);
            })->get()
        ]);
    }

    /**
     * Display a paginated list of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = CupSize::when($request->search, function (Builder $query, string $search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('extra_price', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function show(CupSize $cupSize)
    {
        return response()->json($cupSize);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'extra_price' => 'required|integer',
        ]);

        return CupSize::create([
            'name' => $request->name,
            'extra_price' => $request->extra_price,
        ]);
    }

    public function update(Request $request, CupSize $cupSize)
    {
        $request->validate([
            'name' => 'required',
            'extra_price' => 'required|integer',
        ]);

        $cupSize->update([
            'name' => $request->name,
            'extra_price' => $request->extra_price,
        ]);

        return $cupSize;
    }

    public function destroy(CupSize $cupSize)
    {
        $cupSize->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
