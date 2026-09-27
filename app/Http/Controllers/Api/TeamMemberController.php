<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeamMemberController extends Controller
{
    public function index(Request $request)
    {
        $members = User::where('company_id', $request->user()->company_id)
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $members
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,member',
        ]);

        $member = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'company_id' => $request->user()->company_id,
        ]);

        return response()->json([
            'data' => $member->only('id', 'name', 'email', 'role')
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        if ((int) $id === $request->user()->id) {
            return response()->json(['message' => 'Kendi rolünüzü değiştiremezsiniz.'], 422);
        }

        $member = User::where('company_id', $request->user()->company_id)->find($id);
        if (!$member) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => ['sometimes', Rule::in(['admin', 'member'])],
        ]);

        if (($validated['role'] ?? null) === 'member' && $member->isAdmin() && $this->adminCount($request->user()->company_id) === 1) {
            return response()->json(['message' => 'Son yöneticinin rolü değiştirilemez.'], 422);
        }

        $member->update($validated);

        return response()->json([
            'data' => $member->only('id', 'name', 'email', 'role')
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        if ((int) $id === $request->user()->id) {
            return response()->json(['message' => 'Kendi hesabınızı silemezsiniz.'], 422);
        }

        $member = User::where('company_id', $request->user()->company_id)->find($id);
        if (!$member) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($member->isAdmin() && $this->adminCount($request->user()->company_id) === 1) {
            return response()->json(['message' => 'Son yönetici silinemez.'], 422);
        }

        $member->delete();

        return response()->json(['message' => 'User deleted'], 200);
    }

    private function adminCount(int $companyId): int
    {
        return User::where('company_id', $companyId)->where('role', 'admin')->count();
    }
}