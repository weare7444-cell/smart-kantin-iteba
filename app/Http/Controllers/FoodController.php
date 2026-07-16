<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function byStall($stallId)
    {
        $foods = Food::where('stall_id', $stallId)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($foods);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stall_id' => 'required|integer',
            'name'     => 'required|string|max:100',
            'price'    => 'required|numeric|min:100',
            'category' => 'required|in:makanan,minuman',
        ]);

        $food = Food::create([
            'stall_id' => $data['stall_id'],
            'name'     => $data['name'],
            'price'    => $data['price'],
            'category' => $data['category'],
            'is_ready' => true,
        ]);

        return response()->json($food, 201);
    }

    public function toggleReady($id)
    {
        $food = Food::findOrFail($id);
        $food->update(['is_ready' => !$food->is_ready]);
        return response()->json($food);
    }

    public function destroy($id)
    {
        $food = Food::findOrFail($id);
        $food->delete();
        return response()->json(['message' => 'Menu dihapus']);
    }
}
