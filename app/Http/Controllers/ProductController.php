<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Products::with('supplier')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_code' => 'required|string|max:255|unique:products,material_code',
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $product = Products::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('supplier')
        ], 201);
    }

    public function show(Products $product)
    {
        return response()->json($product->load('supplier'));
    }

    public function update(Request $request, Products $product)
    {
        $validated = $request->validate([
            'material_code' => 'required|string|max:255|unique:products,material_code,' . $product->id,
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('supplier')
        ]);
    }

    public function destroy(Products $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
