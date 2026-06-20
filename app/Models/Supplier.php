<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contactno',
        'address',
    ];

    public function products()
    {
        return $this->hasMany(Products::class, 'supplier_id');
    }
}

