<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchStock extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_invoice_id',
        'no_cases',
        'pack_size',
        'qty',
        'retail_price',
        'netprice',
        'expiry_date',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function supplierInvoice()
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }
}
