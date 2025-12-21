<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Gudang
use App\Http\Controllers\Gudang\DashboardGudangController;
use App\Http\Controllers\Gudang\StokController;
use App\Http\Controllers\Gudang\PurchaseOrderController;

// Manajer
use App\Http\Controllers\Manajer\DashboardManajerController;
use App\Http\Controllers\Manajer\SupplierController;
use App\Http\Controllers\Manajer\UserManagementController;

// Supplier
use App\Http\Controllers\Supplier\SupplierPOController;

// Cabang
use App\Http\Controllers\Cabang\PermintaanCabangController;

// Kurir
use App\Http\Controllers\Kurir\PengirimanController;

use App\Http\Controllers\ProdukController;
use App\Http\Controllers\DashboardController;
/*
|--------------------------------------------------------------------------
| ROUTE LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
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
| ROUTE MANAJER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Manajer'])->group(function () {

    Route::get('/manajer', [DashboardManajerController::class, 'index']);



    Route::get('/manajer/supplier', [SupplierController::class, 'index']);
    Route::post('/manajer/supplier', [SupplierController::class, 'store']);

    Route::get('/manajer/user/create', [UserManagementController::class, 'create']);
    Route::post('/manajer/user', [UserManagementController::class, 'store']);
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

/*
|--------------------------------------------------------------------------
| ROUTE CABANG
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Cabang'])->group(function () {

    Route::get('/cabang', [PermintaanCabangController::class, 'index']);
    Route::post('/cabang/permintaan', [PermintaanCabangController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| ROUTE KURIR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Kurir'])->group(function () {

    Route::get('/kurir', [PengirimanController::class, 'index']);
    Route::post('/kurir/pengiriman/{id}', [PengirimanController::class, 'updateStatus']);
});
