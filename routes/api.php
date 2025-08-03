<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\UmkmController;
use App\Http\Controllers\JenisUmkmController;

/*
|--------------------------------------------------------------------------
| API Routes - Versi Aman, Modern & Siap Produksi
|--------------------------------------------------------------------------
| Semua route otomatis prefiks dengan /api
| Ditambahkan /v1 sebagai penanda versi API
| Digunakan prinsip RESTful & middleware keamanan
*/

Route::prefix('v1')->group(function () {

    //  Login tanpa throttle
    // Route::post('/login', [AuthController::class, 'login']);

    // 📰 Endpoint berita tanpa throttle dan 'verify.origin'
    Route::middleware(['skipThrottle'])->group(function () {
        Route::get('/berita', [BeritaController::class, 'index']);  
        Route::get('/berita-by-id/{id}', [BeritaController::class, 'showById']);
        Route::get('/berita/{slug}', [BeritaController::class, 'showBySlug']);
        Route::get('/umkm', [UmkmController::class, 'index']);
        Route::get('/umkm/{id}', [UmkmController::class, 'show']);
        Route::get('/jenis-umkm', [JenisUmkmController::class, 'index']);
        Route::get('/jenis-umkm/{slug}', [JenisUmkmController::class, 'showBySlug']);
    });

    //  Endpoint yang butuh login ,'verify.origin' dihilangkan
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/berita', [BeritaController::class, 'store']);
        Route::delete('/berita/{id}', [BeritaController::class, 'destroy']);
        Route::post('/berita/{id}', [BeritaController::class, 'update']);
        Route::match(['put', 'patch'], '/berita/{id}', [BeritaController::class, 'update']);

    //jenis  UMKM
        Route::post('/jenis-umkm', [JenisUmkmController::class, 'store']);
        Route::delete('/jenis-umkm/{id}', [JenisUmkmController::class, 'destroy']);
        Route::put('/jenis-umkm/{id}', [JenisUmkmController::class, 'update']);

    // UMKM
        Route::post('/umkm', [UmkmController::class, 'store']);
        Route::match(['put', 'patch'], '/umkm/{id}', [UmkmController::class, 'update']);
        Route::delete('/umkm/{id}', [UmkmController::class, 'destroy']);

    // Tambahan penting untuk verifikasi login dari frontend
    Route::get('/check-auth', function (Request $request) {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user()
        ]);
    });

    Route::post('/change-password', function (Request $request) {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = auth()->user();
        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password berhasil diubah']);
    });
});


    // ❌ Jika salah URL
    Route::fallback(function () {
        return response()->json([
            'message' => 'Halaman tidak ditemukan. Cek kembali URL API kamu.'
        ], 404);
    });
});


