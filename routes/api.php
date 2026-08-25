<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierInvoiceController;
use App\Http\Controllers\BatchStockController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User Management (Admin only checks are inside the controller)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    
    // Supplier Management
    Route::apiResource('suppliers', SupplierController::class);

    // Product Management
    Route::apiResource('products', ProductController::class);

    // Supplier Invoice Management
    Route::get('/supplier-invoices/total-sum', [SupplierInvoiceController::class, 'totalSum']);
    Route::apiResource('supplier-invoices', SupplierInvoiceController::class);
    Route::get('/supplies', [SupplierInvoiceController::class, 'index']);
    Route::post('/supplies', [SupplierInvoiceController::class, 'store']);
    Route::get('/supplies/{supplierInvoice}', [SupplierInvoiceController::class, 'show']);

    // Batch Stock Management
    Route::apiResource('batch-stocks', BatchStockController::class);
});
