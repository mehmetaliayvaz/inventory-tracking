<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'image' => $this->imageUrlRules(),
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
            'image' => $this->imageUrlRules(),
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

    /**
     * Upload a product image. Files are validated for type, size and
     * dimensions before being stored on the public disk.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        $file = $request->file('image');

        // Defense in depth: only known image MIME types are accepted.
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            return response()->json(['errors' => ['image' => ['Unsupported image type.']]], 422);
        }

        // Make sure the file is a real, decodable image and enforce dimension limits.
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            return response()->json(['errors' => ['image' => ['The file is not a valid image.']]], 422);
        }
        [$width, $height] = $imageInfo;
        if ($width > 4096 || $height > 4096) {
            return response()->json(['errors' => ['image' => ['Image dimensions exceed the 4096x4096 limit.']]], 422);
        }

        // store() generates a random file name, so the original (possibly
        // malicious) file name is never kept.
        $path = $file->store('product-images', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ], 201);
    }

    /**
     * Validation rules for the product image. A URL may be an http(s) URL or
     * a same-origin storage path (extension "data:" / "javascript:" and other
     * schemes are rejected to avoid XSS / phishing payloads).
     */
    private function imageUrlRules(): array
    {
        return [
            'nullable',
            'string',
            'max:1024',
            function ($attribute, $value, $fail) {
                if ($value === null || $value === '') {
                    return;
                }
                $isHttpUrl = preg_match('#^https?://#i', $value) === 1;
                $isLocalPath = str_starts_with($value, '/');
                if (!$isHttpUrl && !$isLocalPath) {
                    $fail('The image must be a valid http(s) URL or a storage path.');
                }
            },
        ];
    }
}