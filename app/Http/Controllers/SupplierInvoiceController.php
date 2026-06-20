<?php

namespace App\Http\Controllers;

use App\Models\SupplierInvoice;
use Illuminate\Http\Request;

class SupplierInvoiceController extends Controller
{
    public function index()
    {
        return response()->json(SupplierInvoice::with('supplier')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'total_bill_amount' => 'required|numeric|min:0',
            'is_manual_total' => 'boolean',
        ]);

        $invoice = SupplierInvoice::create($validated);

        return response()->json([
            'message' => 'Supplier invoice created successfully',
            'invoice' => $invoice->load('supplier')
        ], 201);
    }

    public function show(SupplierInvoice $supplierInvoice)
    {
        return response()->json($supplierInvoice->load('supplier'));
    }

    public function update(Request $request, SupplierInvoice $supplierInvoice)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'total_bill_amount' => 'required|numeric|min:0',
            'is_manual_total' => 'boolean',
        ]);

        $supplierInvoice->update($validated);

        return response()->json([
            'message' => 'Supplier invoice updated successfully',
            'invoice' => $supplierInvoice->load('supplier')
        ]);
    }

    public function destroy(SupplierInvoice $supplierInvoice)
    {
        $supplierInvoice->delete();

        return response()->json([
            'message' => 'Supplier invoice deleted successfully'
        ]);
    }
}
