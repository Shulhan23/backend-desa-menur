<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BeritaController;

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

Route::post('/login', function (Request $request) {
    logger('Email: '.$request->email);
    logger('Password: '.$request->password);
    logger('X-XSRF-TOKEN: '.$request->header('X-XSRF-TOKEN'));
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Login gagal'], 401);
    }

    $request->session()->regenerate();

    return response()->json([
        'message' => 'Login berhasil',
        'user' => Auth::user(),
    ]);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logout berhasil']);
});

Route::get('/check-auth', function (Request $request) {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::user(),
    ]);
})->middleware('auth:sanctum');

// ✅ Tambahan supaya DELETE bisa dilakukan
Route::middleware('auth:sanctum')->delete('/laravel-api/api/v1/berita/{id}', [BeritaController::class, 'destroy']);
