<?php

namespace App\Http\Controllers;

use App\Models\BatchStock;
use Illuminate\Http\Request;

class BatchStockController extends Controller
{
    public function index()
    {
        return response()->json(BatchStock::with(['product', 'supplierInvoice'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_invoice_id' => 'required|exists:supplier_invoices,id',
            'no_cases' => 'required|integer|min:0',
            'pack_size' => 'required|integer|min:0',
            'qty' => 'required|integer|min:0',
            'retail_price' => 'required|numeric|min:0',
            'netprice' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $batchStock = BatchStock::create($validated);

        return response()->json([
            'message' => 'Batch stock created successfully',
            'batch_stock' => $batchStock->load(['product', 'supplierInvoice'])
        ], 201);
    }

    public function show(BatchStock $batchStock)
    {
        return response()->json($batchStock->load(['product', 'supplierInvoice']));
    }

    public function update(Request $request, BatchStock $batchStock)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_invoice_id' => 'required|exists:supplier_invoices,id',
            'no_cases' => 'required|integer|min:0',
            'pack_size' => 'required|integer|min:0',
            'qty' => 'required|integer|min:0',
            'retail_price' => 'required|numeric|min:0',
            'netprice' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $batchStock->update($validated);

        return response()->json([
            'message' => 'Batch stock updated successfully',
            'batch_stock' => $batchStock->load(['product', 'supplierInvoice'])
        ]);
    }

    public function destroy(BatchStock $batchStock)
    {
        $batchStock->delete();

        return response()->json([
            'message' => 'Batch stock deleted successfully'
        ]);
    }
}
