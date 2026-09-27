<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => Category::where('company_id', $request->user()->company_id)
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
                Rule::unique('categories', 'name')->where('company_id', $request->user()->company_id),
            ],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'company_id' => $request->user()->company_id,
        ]);

        return response()->json([
            'data' => $category
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::where('company_id', $request->user()->company_id)->find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($category->id),
            ],
        ]);

        $category->update($validated);

        return response()->json([
            'data' => $category
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $category = Category::where('company_id', $request->user()->company_id)->find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted'], 200);
    }
}