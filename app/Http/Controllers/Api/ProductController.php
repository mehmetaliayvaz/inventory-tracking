<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('user_id', $request->user()->id);

        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'data' => $items
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'barcode' => 'nullable|string|max:64',
            'brand' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:1024',
        ]);

        $product = new Product($validated);
        $product->user_id = $request->user()->id;
        $product->save();

        return response()->json([
            'data' => $product
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $product = Product::where('user_id', $request->user()->id)->find($id);
        if ($product) {
            return response()->json([
                'data' => $product
            ], 200);
        }

        return response()->json([
            'message' => 'Product not found'
        ], 404);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::where('user_id', $request->user()->id)->find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'barcode' => 'nullable|string|max:64',
            'brand' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:1024',
        ]);

        $product->update($validated);

        return response()->json([
            'data' => $product
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $product = Product::where('user_id', $request->user()->id)->find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted'
        ], 200);
    }
}