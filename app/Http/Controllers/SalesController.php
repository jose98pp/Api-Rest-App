<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    // Registrar una nueva venta
    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $total = 0;
            $items = [];

            foreach ($request->products as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("No hay suficiente stock para {$product->name}");
                }

                $product->decrement('stock', $item['quantity']);

                $total += $product->price * $item['quantity'];

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];
            }

            $sale = Sale::create([
                'user_id' => auth('api')->id(),
                'total' => $total,
            ]);

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Venta registrada correctamente', 'sale' => $sale->load('items')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Listar todas las ventas
    public function index()
    {
        $sales = Sale::with(['items.product', 'user'])->orderByDesc('created_at')->get();
        return response()->json($sales);
    }

    // Ver una venta por ID
    public function show($id)
    {
        $sale = Sale::with(['items.product', 'user'])->find($id);
        if (!$sale) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }
        return response()->json($sale);
    }
}
