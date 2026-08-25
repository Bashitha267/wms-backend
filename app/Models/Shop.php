<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    //
    protected $fillable = [
        'shop_code',
        'shop_name',
        'address',
        'phoneno',
        'route_id',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}

