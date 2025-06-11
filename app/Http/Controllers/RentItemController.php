<?php
namespace App\Http\Controllers;

use App\Models\RentItemPoster;
use Illuminate\Http\Request;
use App\Models\RentItem;
use App\Models\RentItemImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class RentItemController extends Controller
{
    public function storeWithPoster(Request $request)
    {
        // 1) Walidacja wszystkich kluczy zgodnie z tym, co wysyła Angular:
        $validated = $request->validate([
            // --- pola RentItem ---
            'userId'       => 'required|integer',
            'title'        => 'required|string|max:255',
            'address'      => 'required|string|max:255',
            'transmission' => 'required|string|max:255',
            'seats'        => 'required|integer',
            'power'        => 'required|integer',
            'year'         => 'required|integer',
            'price'        => 'required|numeric',
            'description'  => 'required|string',

            // --- galeria plików dla RentItem: imageUrl[] ---
            'imageUrl'        => 'required|array|min:1',
            'imageUrl.*'      => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            // --- dane dla RentItemPoster: pola location i rating ---
            'location'         => 'required|string|max:255',
            'rating'           => 'required|numeric',

            // --- zdjęcia-okładki: coverImageUrl[] (u Ciebie to File[]) ---
            'coverImageUrl'    => 'required|array|min:1',
            'coverImageUrl.*'  => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // 2) Start transakcji, żeby w razie błędu wszystko się cofnęło
        DB::beginTransaction();
        try {
            // 3) Tworzymy najpierw “główny” RentItem
            $item = RentItem::create([
                'userId'       => $validated['userId'],
                'title'        => $validated['title'],
                'address'      => $validated['address'],
                'transmission' => $validated['transmission'],
                'seats'        => $validated['seats'],
                'power'        => $validated['power'],
                'year'         => $validated['year'],
                'price'        => $validated['price'],
                'description'  => $validated['description'],
            ]);

            // 4) Zapisujemy w pętli wszystkie pliki z “imageUrl[]” jako RentItemImage
            foreach ($request->file('imageUrl') as $file) {
                $path = $file->store('rent_images', 'public');
                RentItemImage::create([
                    'rent_item_id' => $item->id,
                    'image_path'   => $path,
                ]);
            }

            // 5) Teraz tworzymy “cover” (RentItemPoster). Pobieramy tylko PIERWSZY plik z coverImageUrl[]
            $coverFiles = $request->file('coverImageUrl');
            $coverImagePath = null;
            if (is_array($coverFiles) && count($coverFiles) > 0) {
                // bierzemy pierwsze zdjęcie-okładkę
                $coverImagePath = $coverFiles[0]->store('rent_images', 'public');
            }

            $poster = RentItemPoster::create([
                'title'        => $validated['title'],       // jeśli tytuł covera ma być taki sam jak item
                'location'     => $validated['location'],
                'transmission' => $validated['transmission'], // możesz też użyć osobnego pola, ale najczęściej to samo
                'seats'        => $validated['seats'],
                'power'        => $validated['power'],
                'year'         => $validated['year'],
                'price'        => $validated['price'],
                'rating'       => $validated['rating'],
                'image_path'   => $coverImagePath,
                'rent_item_id' => $item->id,
            ]);

            DB::commit();

            return response()->json([
                'message'   => 'RentItem i RentItemPoster zostały pomyślnie utworzone',
                'item_id'   => $item->id,
                'poster_id' => $poster->id,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Błąd podczas tworzenia RentItem z coverem',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'transmission' => 'required|string|max:255',
            'seats' => 'required|integer',
            'power' => 'required|integer',
            'year' => 'required|integer',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Utwórz item
        $item = RentItem::create([
            'title' => $validated['title'],
            'address' => $validated['address'],
            'transmission' => $validated['transmission'],
            'seats' => $validated['seats'],
            'power' => $validated['power'],
            'year' => $validated['year'],
            'price' => $validated['price'],
            'description' => $validated['description'],
        ]);

        // Zapisz zdjęcia (galerię)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('rent_images', 'public');
                RentItemImage::create([
                    'rent_item_id' => $item->id,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json(['message' => 'Item created', 'item_id' => $item->id]);
    }

    public function index()
    {
        $items = RentItem::with(['images', 'poster'])->get();

        $result = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'userId' => $item->userId,
                'title' => $item->title,
                'address' => $item->address,
                'transmission' => $item->transmission,
                'seats' => $item->seats,
                'power' => $item->power,
                'year' => $item->year,
                'price' => $item->price,
                'description' => $item->description,
                'images' => $item->images->map(fn($img) => asset('storage/' . $img->image_path)),
            ];
        });

        return response()->json($result);
    }

    public function show($id)
    {
        $item = RentItem::with('images')->find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json([
            'id' => $item->id,
            'userId' => $item->userId,
            'title' => $item->title,
            'address' => $item->address,
            'transmission' => $item->transmission,
            'seats' => $item->seats,
            'power' => $item->power,
            'year' => $item->year,
            'price' => $item->price,
            'description' => $item->description,
            'imageUrl' => $item->images->map(fn($img) => asset('storage/' . $img->image_path)),
        ]);
    }

    public function destroy($id)
    {
        $item = RentItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        foreach ($item->images as $img) {
            if (Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }

        if ($item->poster && Storage::disk('public')->exists($item->poster->image_path)) {
            Storage::disk('public')->delete($item->poster->image_path);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
