<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'data' => $orders
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.barcode' => 'nullable|string|max:64',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($request, $validated) {
            $total = collect($validated['items'])->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => $total,
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'name' => $item['name'],
                    'barcode' => $item['barcode'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);

                if (!empty($item['product_id'])) {
                    $product = Product::where('user_id', $request->user()->id)
                        ->find($item['product_id']);
                    if ($product && $product->stock >= $item['quantity']) {
                        $product->decrement('stock', $item['quantity']);
                    }
                }
            }

            return $order;
        });

        return response()->json([
            'data' => $order->load('items')
        ], 201);
    }
}