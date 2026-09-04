<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'route_id',
        'route_name',
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }
}
