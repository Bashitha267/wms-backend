<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ping', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ok',
            'message' => 'pong',
            'database' => [
                'connected' => true,
                'name' => DB::connection()->getDatabaseName(),
            ],
            'timestamp' => now()->toIso8601String(),
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'pong',
            'database' => [
                'connected' => false,
                'error' => $e->getMessage(),
            ],
            'timestamp' => now()->toIso8601String(),
        ], 500);
    }
});
