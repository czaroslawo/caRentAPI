<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentItemPoster extends Model
{
    protected $fillable = [
        'title',
        'location',
        'transmission',
        'seats',
        'power',
        'year',
        'price',
        'rating',
        'image_path',
        'rent_item_id'
    ];

    public function item()
    {
        return $this->belongsTo(RentItem::class, 'rent_item_id');
    }
}
