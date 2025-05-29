<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentItemPoster extends Model
{
    protected $fillable = [
        'title', 'location', 'transmission', 'seats',
        'power', 'year', 'price', 'rating', 'image_path'
    ];
}
