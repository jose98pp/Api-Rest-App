<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;

class Dashboard extends Model
{
    protected $table = null; // No tiene tabla

    public static function getDashboardData()
    {
        // Total de productos (global)
        $productsCount = Product::count();

        // Total de ventas (global)
        $salesCount = Sale::count();

        // Total de ingresos (suma de total en la tabla sales, global)
        $totalRevenue = Sale::sum('total');

        // Ventas agrupadas por fecha (últimos 7 días, global)
        $salesByDate = Sale::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Últimos productos (global)
        $recentProducts = Product::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Últimas ventas con detalles de productos (global)
        $recentSales = SaleItem::with(['sale', 'product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($saleItem) {
                return [
                    'sale_id' => $saleItem->sale_id,
                    'product_name' => $saleItem->product ? $saleItem->product->name : 'Producto eliminado',
                    'quantity' => $saleItem->quantity,
                    'total_price' => $saleItem->quantity * $saleItem->price,
                ];
            });

        return [
            'products_count' => $productsCount,
            'sales_count' => $salesCount,
            'total_revenue' => $totalRevenue,
            'sales_by_date' => $salesByDate,
            'recent_products' => $recentProducts,
            'recent_sales' => $recentSales,
        ];
    }
}