<?php

namespace App\Http\Controllers;

use App\Models\RentItemPoster;
use Illuminate\Http\Request;

class RentItemPosterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'transmission' => 'required|string|max:255',
            'seats' => 'required|integer',
            'power' => 'required|integer',
            'year' => 'required|integer',
            'price' => 'required|numeric',
            'rating' => 'required|numeric',
            'imageUrl.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('imageUrl')) {
            $imagePath = $request->file('imageUrl')->store('rent_images', 'public');
        }


        $item = RentItemPoster::create([
            'title' => $validated['title'],
            'location' => $validated['location'],
            'transmission' => $validated['transmission'],
            'seats' => $validated['seats'],
            'power' => $validated['power'],
            'year' => $validated['year'],
            'price' => $validated['price'],
            'rating' => $validated['rating'],
            'image_path' => $imagePath,
        ]);

        // Optionally: store image paths in another table or JSON column

        return response()->json(['message' => 'Item created', 'item' => $item]);
    }

    public function index()
    {

        $items = RentItemPoster::all();

        $result = $items->map(function($item) {
            return [
                'id' => $item->id,
                'imageUrl' => $item->image_path ? asset('storage/' . $item->image_path) : null, // pełny URL do zdjęcia
                'title' => $item->title,
                'location' => $item->location,
                'transmission' => $item->transmission,
                'seats' => $item->seats,
                'power' => $item->power,
                'year' => $item->year,
                'price' => $item->price,
                'rating' => $item->rating,
            ];
        });

        return response()->json($result);
    }

    public function destroy($id)
    {
        $item = RentItemPoster::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        // Usunięcie obrazka z dysku, jeśli istnieje
        if ($item->image_path && \Storage::disk('public')->exists($item->image_path)) {
            \Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
