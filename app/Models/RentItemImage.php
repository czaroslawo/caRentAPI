<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentItemImage extends Model
{
    protected $fillable = ['rent_item_id', 'image_path'];

    public function item()
    {
        return $this->belongsTo(RentItem::class);
    }
}
