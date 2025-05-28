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

        $images = [];
        if ($request->hasFile('imageUrl')) {
            foreach ($request->file('imageUrl') as $file) {
                $path = $file->store('rent_images', 'public');
                $images[] = $path;
            }
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
        ]);

        // Optionally: store image paths in another table or JSON column

        return response()->json(['message' => 'Item created', 'item' => $item]);
    }
}
