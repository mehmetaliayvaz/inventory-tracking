<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'brand')
            ->where('company_id', $request->user()->company_id);

        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($q) {
                        $brandQuery->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($q) {
                        $categoryQuery->where('name', 'like', "%{$q}%");
                    });
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
            'image' => 'nullable|string|max:1024',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
        ]);

        $product = new Product($validated);
        $product->user_id = $request->user()->id;
        $product->company_id = $request->user()->company_id;
        $product->save();

        return response()->json([
            'data' => $product->load('category', 'brand')
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $product = Product::with('category', 'brand')
            ->where('company_id', $request->user()->company_id)
            ->find($id);

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
        $product = Product::where('company_id', $request->user()->company_id)->find($id);
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
            'image' => 'nullable|string|max:1024',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
        ]);

        $product->update($validated);

        return response()->json([
            'data' => $product->load('category', 'brand')
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $product = Product::where('company_id', $request->user()->company_id)->find($id);
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