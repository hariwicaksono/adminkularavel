<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $role = $request->input('role');
        $status = $request->input('status');

        $query = User::with('roles')
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->when($role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $role)))
            ->when(!is_null($status), fn($q) => $q->where('status', $status));

        $users = $query->paginate($request->input('per_page', 10));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'status' => 'required|boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);
        return response()->json($user);
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'status' => 'required|boolean',
        ]);

        if ($validated['password']) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = !$user->status;
        $user->save();

        return response()->json(['status' => $user->status]);
    }

    public function updateRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $roleIds = \App\Models\Role::whereIn('name', $request->roles)->pluck('id');
        $user->roles()->sync($roleIds);

        return response()->json(['message' => 'Roles updated']);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $shouldRelogin = false;

        if ($user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $shouldRelogin = true;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        /*  if ($emailChanged) {
            JWTAuth::invalidate(JWTAuth::getToken()); // logout current token
            return response()->json([
                'message' => 'Email changed, please re-login',
                'relogin' => true
            ], 200);
        } */

        return response()->json([
            'message' => 'Profile updated successfully',
            'relogin' => $shouldRelogin,
            'user' => $user
        ]);
    }
}
