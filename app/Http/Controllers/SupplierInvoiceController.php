<?php

namespace App\Http\Controllers;

use App\Models\BatchStock;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = SupplierInvoice::with(['supplier', 'batchStocks.product'])->latest()->get();
        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'total_bill_amount' => 'required|numeric|min:0',
            'is_manual_total' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.no_cases' => 'required_with:items|integer|min:0',
            'items.*.pack_size' => 'required_with:items|integer|min:0',
            'items.*.qty' => 'required_with:items|integer|min:0',
            'items.*.retail_price' => 'required_with:items|numeric|min:0',
            'items.*.netprice' => 'required_with:items|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $invoice = SupplierInvoice::create([
                'supplier_id' => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'total_bill_amount' => $validated['total_bill_amount'],
                'is_manual_total' => $validated['is_manual_total'] ?? false,
            ]);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    BatchStock::create([
                        'supplier_invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'],
                        'no_cases' => $item['no_cases'],
                        'pack_size' => $item['pack_size'],
                        'qty' => $item['qty'],
                        'retail_price' => $item['retail_price'],
                        'netprice' => $item['netprice'],
                        'expiry_date' => $item['expiry_date'] ?? null,
                    ]);
                }
            }

            return $invoice;
        });

        return response()->json($invoice->load(['supplier', 'batchStocks.product']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = SupplierInvoice::with(['supplier', 'batchStocks.product'])->findOrFail($id);
        return response()->json($invoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoice = SupplierInvoice::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'invoice_number' => 'sometimes|required|string|max:255',
            'invoice_date' => 'sometimes|required|date',
            'total_bill_amount' => 'sometimes|required|numeric|min:0',
            'is_manual_total' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.no_cases' => 'required_with:items|integer|min:0',
            'items.*.pack_size' => 'required_with:items|integer|min:0',
            'items.*.qty' => 'required_with:items|integer|min:0',
            'items.*.retail_price' => 'required_with:items|numeric|min:0',
            'items.*.netprice' => 'required_with:items|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        $invoice = DB::transaction(function () use ($invoice, $validated) {
            $invoice->update([
                'supplier_id' => $validated['supplier_id'] ?? $invoice->supplier_id,
                'invoice_number' => $validated['invoice_number'] ?? $invoice->invoice_number,
                'invoice_date' => $validated['invoice_date'] ?? $invoice->invoice_date,
                'total_bill_amount' => $validated['total_bill_amount'] ?? $invoice->total_bill_amount,
                'is_manual_total' => $validated['is_manual_total'] ?? $invoice->is_manual_total,
            ]);

            if (isset($validated['items'])) {
                $invoice->batchStocks()->delete();
                foreach ($validated['items'] as $item) {
                    BatchStock::create([
                        'supplier_invoice_id' => $invoice->id,
                        'product_id' => $item['product_id'],
                        'no_cases' => $item['no_cases'],
                        'pack_size' => $item['pack_size'],
                        'qty' => $item['qty'],
                        'retail_price' => $item['retail_price'],
                        'netprice' => $item['netprice'],
                        'expiry_date' => $item['expiry_date'] ?? null,
                    ]);
                }
            }

            return $invoice;
        });

        return response()->json($invoice->load(['supplier', 'batchStocks.product']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $invoice = SupplierInvoice::findOrFail($id);
        
        DB::transaction(function () use ($invoice) {
            // Delete associated batch stocks first to avoid foreign key constraints
            $invoice->batchStocks()->delete();
            $invoice->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * Calculate total inventory value.
     */
    public function totalSum()
    {
        // Total value of items physically in the warehouse (shelf stock)
        $shelfValue = (float) BatchStock::sum(DB::raw('qty * netprice'));

        // Total value of items currently on trucks but not yet delivered (pending manifests)
        $pendingValue = 0;
        if (Schema::hasTable('load_list_items') && Schema::hasTable('loadings')) {
            $pendingValue = (float) DB::table('load_list_items')
                ->join('loadings', 'load_list_items.loading_id', '=', 'loadings.id')
                ->join('batch_stocks', 'load_list_items.batch_id', '=', 'batch_stocks.id')
                ->where('loadings.status', 'pending')
                ->sum(DB::raw('load_list_items.qty * batch_stocks.netprice'));
        }

        $total = $shelfValue + $pendingValue;

        return response()->json(['total' => $total]);
    }
}
