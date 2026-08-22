<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\BatchStock;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function getStats()
    {
        $totalRevenue = 0;
        $manifestCount = 0;
        $dailyRevenue = collect();

        if (Schema::hasTable('loadings') && Schema::hasTable('load_list_items')) {
            $deliveredLoadingIds = DB::table('loadings')->where('status', 'delivered')->pluck('id');
            $manifestCount = count($deliveredLoadingIds);

            if (count($deliveredLoadingIds) > 0) {
                $totalRevenue = (float) DB::table('load_list_items')
                    ->whereIn('loading_id', $deliveredLoadingIds)
                    ->sum(DB::raw('qty * net_price'));
            }

            $dailyRevenue = DB::table('loadings')
                ->where('status', 'delivered')
                ->where('loading_date', '>=', now()->subDays(30))
                ->join('load_list_items', 'loadings.id', '=', 'load_list_items.loading_id')
                ->select(
                    'loadings.loading_date as date',
                    DB::raw('SUM(load_list_items.qty * load_list_items.net_price) as revenue')
                )
                ->groupBy('loadings.loading_date')
                ->get()
                ->keyBy('date');
        }

        // Total Supply Cost (from Supplier Invoices)
        $totalSupplyCost = 0;
        $dailyProfit = collect();

        if (Schema::hasTable('supplier_invoices')) {
            $totalSupplyCost = (float) SupplierInvoice::sum('total_bill_amount');

            $dailyProfit = SupplierInvoice::where('invoice_date', '>=', now()->subDays(30))
                ->select(
                    'invoice_date as date',
                    DB::raw('SUM(total_bill_amount * 0.05) as profit')
                )
                ->groupBy('invoice_date')
                ->get()
                ->keyBy('date');
        }

        // Profit is a flat 5% commission on the total supply amount
        $totalProfit = $totalSupplyCost * 0.05;

        // Combine for chart
        $dates = $dailyRevenue->keys()->concat($dailyProfit->keys())->unique()->sort();
        $dailyStats = $dates->map(function ($date) use ($dailyRevenue, $dailyProfit) {
            return [
                'loading_date' => $date,
                'revenue' => (float) ($dailyRevenue[$date]->revenue ?? 0),
                'profit' => (float) ($dailyProfit[$date]->profit ?? 0),
            ];
        })->values();

        // Low Stock Analysis - Threshold (50 units)
        $lowStockCount = 0;
        if (Schema::hasTable('products') && Schema::hasTable('batch_stocks')) {
            $products = Products::all();
            $hasLoadList = Schema::hasTable('load_list_items') && Schema::hasTable('loadings');

            $lowStockCount = $products->filter(function ($p) use ($hasLoadList) {
                $stock = BatchStock::where('product_id', $p->id)->sum('qty');
                $pending = 0;

                if ($hasLoadList) {
                    $pending = DB::table('load_list_items')
                        ->join('loadings', 'load_list_items.loading_id', '=', 'loadings.id')
                        ->where('loadings.status', 'pending')
                        ->whereIn('batch_id', BatchStock::where('product_id', $p->id)->pluck('id'))
                        ->sum('qty');
                }

                $total = (int) $stock + (int) $pending;
                return $total > 0 && $total <= 50;
            })->count();
        }

        return response()->json([
            'total_revenue' => (float) $totalRevenue,
            'total_cost' => (float) $totalRevenue,
            'total_profit' => (float) $totalProfit,
            'total_supply_cost' => (float) $totalSupplyCost,
            'manifest_count' => $manifestCount,
            'daily_stats' => $dailyStats,
            'low_stock_count' => $lowStockCount,
        ]);
    }
}
