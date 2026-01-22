<?php

namespace App\Http\Controllers;

use App\Models\SugarLevel;
use Illuminate\Http\Request;

class SugarLevelController extends Controller
{
    public function index()
    {
        return SugarLevel::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        return SugarLevel::create([
            'name' => $request->name,
        ]);
    }

    public function update(Request $request, SugarLevel $sugarLevel)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $sugarLevel->update([
            'name' => $request->name,
        ]);

        return $sugarLevel;
    }

    public function destroy(SugarLevel $sugarLevel)
    {
        $sugarLevel->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
