<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company_Rep extends Model
{
    protected $fillable = [
        'rep_id',
        'rep_name',
        'contact_no',
        'join_date',
        'supplier_id',
        'route_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
