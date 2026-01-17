<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Gudang
use App\Http\Controllers\Gudang\DashboardGudangController;
use App\Http\Controllers\Gudang\StokController;

// Supplier
use App\Http\Controllers\Supplier\SupplierPOController;

use App\Http\Controllers\ProdukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JenisProdukController;
use App\Http\Controllers\Laporan\PengirimanController as LaporanPengirimanController;
use App\Http\Controllers\Laporan\PurchaseOrderController as LaporanPurchaseOrderController;
use App\Http\Controllers\Laporan\ReturController as LaporanReturController;
use App\Http\Controllers\PaymentListController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PermintaanCabangController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\UserSettingController;

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

    Route::middleware(['role:Manajer|Gudang|Cabang'])->prefix('produk')->name('produk')->controller(ProdukController::class)->group(function () {
        Route::get('/form/{id?}', 'form')->name('.form');
        Route::post('/save/{id?}', 'save')->name('.save');
        Route::post('/stok/cabang/{id}', 'saveStokCabang')->name('.stokcabang');
        Route::delete('/produk/{id}', 'delete')->name('.delete');
        Route::get('/', 'index');
    });

    Route::middleware(['role:Manajer'])->prefix('user-management')->name('usermanagement')->controller(UserManagementController::class)->group(function () {
        Route::get('/form/{id?}', 'form')->name('.form');
        Route::post('/save/{id?}', 'save')->name('.save');
        Route::get('/', 'index');
    });

    Route::middleware(['role:Gudang|Cabang'])->prefix('permintaan-cabang')->name('permintaancabang')->controller(PermintaanCabangController::class)->group(function () {
        Route::get('/form/{id?}', 'form')->name('.form');
        Route::post('/store', 'store')->name('.store');
        Route::post('{id}/status', 'updateStatus')->name('.updatestatus');

        Route::get('/', 'index');
    });

    Route::middleware(['role:Gudang|Cabang|Kurir'])->prefix('pengiriman')->name('pengiriman')->controller(PengirimanController::class)->group(function () {
        Route::post('ambil', 'ambil')->name('.ambil');
        Route::post('gagal', 'gagal')->name('.gagal');
        Route::post('diterima', 'diterima')->name('.diterima');
        Route::get('/', 'index');
    });

    Route::middleware(['role:Manajer|Gudang|Supplier'])->prefix('purchase-order')->name('purchaseorder')->controller(PurchaseOrderController::class)->group(function () {
        Route::get('/create',  'create')->name('.create');
        Route::get('/{po}/edit',  'edit')->name('.edit');
        Route::post('/store',  'store')->name('.store');
        Route::put('/{po}/update',  'update')->name('.update');
        Route::post('/{po}/update/status', 'updateStatus')->name('.update.status');

        Route::get('/', 'index');
    });

    Route::middleware(['role:Manajer|Gudang|Supplier'])->prefix('invoice')->name('invoice')->controller(InvoiceController::class)->group(function () {
        Route::post('/create/{poId}', 'createFromPo')->name('.create');
        Route::post('/payment/{invoiceId}', 'savePayment')->name('.payment');
        Route::post('/reject/{invoiceId}', 'reject')->name('.reject');
    });

    Route::middleware(['role:Manajer|Gudang|Supplier'])->prefix('retur')->name('retur')->controller(ReturController::class)->group(function () {
        Route::post('{id}/tolak', 'tolak')->name('.tolak');
        Route::post('{id}/terima', 'terimaRetur')->name('.terima');

        Route::post('payment', 'storeReturPayment')->name('.store.payment');
        Route::post('payment/bayar', 'bayar')->name('.pay.payment');

        Route::post('{id}/kirim', 'kirimBarang')->name('.kirim');
        Route::post('{id}/selesai', 'selesai')->name('.selesai');

        Route::post('store', 'store')->name('.store');
        Route::get('create', 'create')->name('.create');
        Route::get('/', 'index');
    });

    Route::middleware(['role:Manajer|Supplier'])->prefix('payment-list')->name('paymentlist')->controller(PaymentListController::class)->group(function () {
        Route::delete('delete{id}', 'destroy')->name('.delete');
        Route::post('save/{id?}', 'save')->name('.save');
        Route::get('form/{id?}', 'form')->name('.form');
        Route::get('/', 'index');
    });

    Route::middleware(['role:Manajer'])->prefix('jenis-produk')->name('jenisproduk')->controller(JenisProdukController::class)->group(function () {
        Route::post('save', 'save')->name('.save');
        Route::get('/', 'index');
    });

    Route::prefix('setting')->name('setting')->group(function () {
        Route::prefix('user')->name('.user')->controller(UserSettingController::class)->group(function () {
            Route::post('update', 'update')->name('.update');
            Route::get('/', 'index');
        });
    });

    Route::middleware(['role:Owner'])->prefix('laporan')->name('laporan')->group(function () {
        Route::prefix('purchase-order')->name('.purchaseorder')->controller(LaporanPurchaseOrderController::class)->group(function () {
            Route::get('export/pdf', 'exportPdf')->name('.pdf');
            Route::get('/', 'index');
        });

        Route::prefix('retur')->name('.retur')->controller(LaporanReturController::class)->group(function () {
            Route::get('export/pdf', 'exportPdf')->name('.pdf');
            Route::get('/', 'index');
        });

        Route::prefix('pengiriman')->name('.pengiriman')->controller(LaporanPengirimanController::class)->group(function () {
            Route::get('export/pdf', 'exportPdf')->name('.pdf');
            Route::get('/', 'index');
        });
    });
});
