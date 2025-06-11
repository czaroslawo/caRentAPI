<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentItem extends Model
{
    protected $fillable = [
        'userId', 'title', 'address', 'transmission', 'seats',
        'power', 'year', 'price', 'description'
    ];

    public function images()
    {
        return $this->hasMany(RentItemImage::class);
    }

    public function poster()
    {
        return $this->hasOne(RentItemPoster::class, 'rent_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
