<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Sales & Financial Reports Dashboard
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $ordersQuery = Order::whereNotIn('order_status', ['canceled', 'unconfirmed'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $totalSales = (float) $ordersQuery->sum('order_amount');
        $ordersCount = $ordersQuery->count();
        $discountsGiven = (float) $ordersQuery->sum('coupon_discount_amount');

        // Estimate Net Profit (gross sales minus estimated product baseline cost 60%)
        $estimatedCost = $totalSales * 0.58;
        $netProfit = max(0, $totalSales - $estimatedCost - $discountsGiven);

        // Daily breakdown
        $dailyReports = Order::whereNotIn('order_status', ['canceled', 'unconfirmed'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->select(
                DB::raw("DATE(created_at) as report_date"),
                DB::raw("COUNT(*) as total_orders"),
                DB::raw("SUM(order_amount) as total_amount")
            )
            ->groupBy('report_date')
            ->orderByDesc('report_date')
            ->get();

        // Product performance
        $productStats = OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as units_sold'), DB::raw('SUM(price * quantity) as revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('units_sold')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'totalSales',
            'ordersCount',
            'discountsGiven',
            'netProfit',
            'dailyReports',
            'productStats',
            'dateFrom',
            'dateTo'
        ));
    }
}
