<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Total de ventas y monto total facturado.
     */
    public function summary()
    {
        $totalSales = Sale::count();
        $totalRevenue = Sale::sum('total');

        return response()->json([
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue
        ]);
    }

    /**
     * Total de ventas por mes (último año).
     */
    public function salesByMonth()
    {
        $sales = Sale::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json($sales);
    }

    /**
     * Producto más vendido.
     */
    public function topProduct()
    {
        $product = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->first();

        if (!$product) {
            return response()->json(['message' => 'No hay ventas registradas aún.'], 404);
        }

        // Opcional: incluir info del producto
        $productDetails = Product::find($product->product_id);

        return response()->json([
            'product' => $productDetails,
            'total_sold' => $product->total_sold
        ]);
    }
}
