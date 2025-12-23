<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Gudang
use App\Http\Controllers\Gudang\DashboardGudangController;
use App\Http\Controllers\Gudang\StokController;
use App\Http\Controllers\Gudang\PurchaseOrderController;

// Supplier
use App\Http\Controllers\Supplier\SupplierPOController;

use App\Http\Controllers\ProdukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PermintaanCabangController;
use App\Http\Controllers\PengirimanController;

/*
|--------------------------------------------------------------------------
| ROUTE LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ROUTE GENERAL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('produk')->name('produk')->controller(ProdukController::class)->group(function () {
        Route::get('/form/{id?}', 'form')->name('.form');
        Route::post('/save/{id?}', 'save')->name('.save');
        Route::delete('/produk/{id}', 'delete')->name('.delete');
        Route::get('/', 'index');
    });

    Route::middleware(['role:Manajer'])->prefix('user-management')->name('usermanagement')->controller(UserManagementController::class)->group(function () {
        Route::get('/form/{id?}', 'form')->name('.form');
        Route::post('/save/{id?}', 'save')->name('.save');
        Route::get('/', 'index');
    });

    Route::prefix('permintaan-cabang')->name('permintaancabang')->controller(PermintaanCabangController::class)->group(function () {
        Route::get('/form/{id?}', 'form')->name('.form');
        Route::post('/store', 'store')->name('.store');
        Route::post('{id}/status', 'updateStatus')->name('.updatestatus');

        Route::get('/', 'index');
    });

    Route::prefix('pengiriman')->name('pengiriman')->controller(PengirimanController::class)->group(function () {
        Route::get('ambil', 'ambil')->name('.ambil');
        Route::get('/', 'index');
    });
});

/*
|--------------------------------------------------------------------------
| ROUTE GUDANG
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Gudang'])->group(function () {

    Route::get('/gudang', [DashboardGudangController::class, 'index']);

    Route::get('/gudang/stok', [StokController::class, 'index']);

    Route::get('/gudang/po/create', [PurchaseOrderController::class, 'create']);
    Route::post('/gudang/po', [PurchaseOrderController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| ROUTE SUPPLIER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Supplier'])->group(function () {

    Route::get('/supplier', [SupplierPOController::class, 'index']);
    Route::post('/supplier/po/{id}', [SupplierPOController::class, 'updateStatus']);
});
