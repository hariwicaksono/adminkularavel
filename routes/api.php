<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.refresh');

Route::get('/settings/app', function () {
    $settings = \App\Models\Setting::pluck('value', 'key');
    return response()->json($settings);
});

// Protected Routes (require JWT token)
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Contoh route tambahan (misalnya untuk dashboard, produk, dll)
    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'You are authenticated',
            'user' => auth()->user(),
        ]);
    });

    // User Management
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:user.view');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:user.create');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:user.view');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:user.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:user.delete');
    Route::patch('/users/{id}/status', [UserController::class, 'toggleStatus'])->middleware('permission:user.update');
    Route::put('/users/{id}/roles', [UserController::class, 'updateRoles']);

    Route::get('/profile', function () {
        $user = auth()->user();
        return response()->json([
            'user' => $user,
            'roles' => $user->roles->pluck('name'),
        ]);
    });
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // Permission Management
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    // Role Management
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:role.view');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:role.create');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('permission:role.view');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:role.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:role.delete');
    Route::put('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->middleware('permission:role.update');

    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('permission:setting.view');
    Route::post('/settings/{id}', [SettingsController::class, 'update'])->middleware('permission:setting.update');

    Route::get('/logs', [\App\Http\Controllers\Api\LogController::class, 'index']);
    Route::get('/logs/export', [\App\Http\Controllers\Api\LogController::class, 'export']);
    Route::get('/logs/export-pdf', [\App\Http\Controllers\Api\LogController::class, 'exportPdf']);
    Route::get('/logs/print', [\App\Http\Controllers\Api\LogController::class, 'print']);
});
