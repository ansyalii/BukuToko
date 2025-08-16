<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SPK\SPKController;
use App\Http\Controllers\SPK\KriteriaController;
use App\Http\Controllers\SPK\AlternatifController;


Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard.index') : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



Route::middleware(['auth'])->group(function () {
    Route::resource('produk', ProdukController::class)->except(['show']);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/transaksi', [LaporanController::class, 'transaksi'])->name('laporan.transaksi');
    Route::post('/laporan/transaksi', [LaporanController::class, 'store'])->name('laporan.transaksi.store');
    Route::get('/laporan/print', [App\Http\Controllers\LaporanController::class, 'print'])->name('laporan.print');
    Route::post('/laporan/tambah-modal', [App\Http\Controllers\LaporanController::class, 'storeModal'])->name('laporan.tambahModal');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/spk', [SPKController::class, 'index'])->name('spk.index');
    Route::get('/spk/kriteria', [KriteriaController::class, 'index'])->name('spk.kriteria');
    Route::post('/spk/kriteria', [KriteriaController::class, 'proses'])->name('spk.kriteria.proses');
    Route::get('/alternatif', [AlternatifController::class, 'index'])->name('spk.alternatif');
    Route::post('/alternatif', [AlternatifController::class, 'simpan'])->name('spk.alternatif.simpan');
    Route::get('/spk/wp', [SPKController::class, 'hasilWP'])->name('spk.wp');
    Route::post('/spk/reset', [SPKController::class, 'reset'])->name('spk.reset');

});