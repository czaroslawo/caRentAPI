<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
protected  $fillable = [
    'rent_item_id', 'user_id', 'start_date', 'end_date', 'status'
];

    public function rent_item() {
        return $this->belongsTo(RentItem::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
