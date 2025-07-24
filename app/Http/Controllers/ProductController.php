<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;

class ProductController extends BaseController
{
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|min:3|max:100',
            'price'        => 'required|numeric',
            'category_id'  => 'nullable|exists:categories,id',
            'stock'        => 'nullable|integer',
            'image'        => 'nullable|image|max:2048', // imagen máx. 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = new Product($validator->validated());

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public'); // se guarda en storage/app/public/products
            $product->image = $path;
            $product->image_url = Storage::disk('public')->url($path); // Añade la URL pública
        }

        $product->save();

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    public function getProducts(Request $request)
    {
        $query = Product::query();

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }

        // Añade image_url a cada producto
        $products->each(function ($product) {
            if ($product->image) {
                $product->image_url = Storage::disk('public')->url($product->image);
            }
        });

        return response()->json($products, 200);
    }

    public function getProductById($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Añade image_url si existe
        if ($product->image) {
            $product->image_url = Storage::disk('public')->url($product->image);
        }

        return response()->json($product, 200);
    }

    public function updateProductById(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'         => 'sometimes|string|min:3|max:100',
            'price'        => 'sometimes|numeric',
            'category_id'  => 'nullable|exists:categories,id',
            'stock'        => 'nullable|integer',
            'image'        => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->fill($validator->validated());

        if ($request->hasFile('image')) {
            // Elimina la imagen anterior si existe
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
            $product->image_url = Storage::disk('public')->url($path); // Añade la nueva URL
        }

        $product->save();

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    public function deleteProductById($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Elimina la imagen si existe
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
