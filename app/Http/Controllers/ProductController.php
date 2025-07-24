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
        try {
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

            $productData = $validator->validated();
            $product = new Product($productData);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $product->image = $path;
            }

            $product->save();

            // Genera image_url después de guardar
            $responseProduct = $product->toArray();
            if ($product->image) {
                $responseProduct['image_url'] = Storage::disk('public')->url($product->image);
            }

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $responseProduct,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear producto: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno al crear el producto',
                'details' => $e->getMessage(),
            ], 500);
        }
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

        // Genera image_url para cada producto
        $formattedProducts = $products->map(function ($product) {
            $productArray = $product->toArray();
            if ($product->image) {
                $productArray['image_url'] = Storage::disk('public')->url($product->image);
            }
            return $productArray;
        });

        return response()->json($formattedProducts, 200);
    }

    public function getProductById($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $responseProduct = $product->toArray();
        if ($product->image) {
            $responseProduct['image_url'] = Storage::disk('public')->url($product->image);
        }

        return response()->json($responseProduct, 200);
    }

    public function updateProductById(Request $request, $id)
    {
        try {
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
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $path = $request->file('image')->store('products', 'public');
                $product->image = $path;
            }

            $product->save();

            $responseProduct = $product->toArray();
            if ($product->image) {
                $responseProduct['image_url'] = Storage::disk('public')->url($product->image);
            }

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $responseProduct,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar producto: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno al actualizar el producto',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteProductById($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
