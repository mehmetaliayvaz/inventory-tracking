<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => Brand::where('company_id', $request->user()->company_id)
                ->withCount('products')
                ->orderBy('name')
                ->get()
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('brands', 'name')->where('company_id', $request->user()->company_id),
            ],
        ]);

        $brand = Brand::create([
            'name' => $validated['name'],
            'company_id' => $request->user()->company_id,
        ]);

        return response()->json([
            'data' => $brand
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $brand = Brand::where('company_id', $request->user()->company_id)->find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('brands', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($brand->id),
            ],
        ]);

        $brand->update($validated);

        return response()->json([
            'data' => $brand
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $brand = Brand::where('company_id', $request->user()->company_id)->find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $brand->delete();

        return response()->json(['message' => 'Brand deleted'], 200);
    }
}