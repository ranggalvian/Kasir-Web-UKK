<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\DetailPemesananController;
use App\Http\Controllers\RiwayatPemesananController;
use App\Http\Controllers\StatistikController;
use Illuminate\Support\Facades\Route;

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

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
        });

        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
                ->except(['index', 'store']);
        });

        Route::middleware('can:daftar-produk')->group(function () {
            // Route tambahan untuk ambil semua produk dan kategori (tanpa pembatasan permission)
            Route::get('produk/all', [ProdukController::class, 'get'])->withoutMiddleware('can:daftar-produk');
            Route::get('kategori/all', [KategoriController::class, 'get'])->withoutMiddleware('can:daftar-produk');
            Route::put('produk/switch/{id_produk}', [ProdukController::class, 'Ketersediaan']);
            
            // Route lainnya    
            
            Route::get('produk', [ProdukController::class, 'get'])->withoutMiddleware('can:daftar-produk');;
            Route::post('produk', [ProdukController::class, 'index'])->withoutMiddleware('can:daftar-produk');;
            Route::post('produk/store', [ProdukController::class, 'store']);
            Route::apiResource('produk', ProdukController::class)->except(['index', 'store']);
            

        });

        Route::middleware('can:produk-kategori')->group(function () {
            Route::get('kategori', [KategoriController::class, 'get'])->withoutMiddleware('can:produk-kategori');
            Route::post('kategori', [KategoriController::class, 'index'])->withoutMiddleware('can:produk-kategori');
            Route::post('kategori/store', [KategoriController::class, 'store']);
            Route::apiResource('kategori', KategoriController::class)->except(['index', 'store']);
        });
        Route::middleware('can:pembelian-produk')->group(function () {
        Route::get('pembelian', [PembelianController::class, 'index']);
        Route::post('pembelian', [PembelianController::class, 'store']);
        Route::delete('pembelian/{id}', [PembelianController::class, 'destroy']);
        Route::get('pembelian/produk', [PembelianController::class, 'dataProduk']);
        
        });

        Route::middleware('can:pembelian-produk')->group(function () {
            Route::post('riwayat-pemesanan', [RiwayatPemesananController::class, 'store']);
            Route::get('riwayat-pemesanan', [RiwayatPemesananController::class, 'index']);
        });
    });
    Route::prefix('statistik')->group(function () {
    Route::get('/harian', [StatistikController::class, 'harian']);
    Route::get('/grafik', [StatistikController::class, 'grafik']);
    Route::get('/metode', [StatistikController::class, 'metodePembayaran']);
    });

    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::post('/absensi', [AbsensiController::class, 'store']);
    // });
    
});
