<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    Route::get('/smart-kantin', [DashboardController::class, 'mahasiswa'])->name('dashboard.mahasiswa');
});

Route::middleware(['auth', 'role:mahasiswa'])->post('/api/order', [OrderController::class, 'store']);

Route::middleware(['auth', 'role:penjual'])->group(function () {
    Route::get('/penjual/dashboard', [DashboardController::class, 'penjual'])->name('dashboard.penjual');
    Route::get('/penjual/laporan', [DashboardController::class, 'laporan'])->name('laporan.penjual');
    Route::get('/penjual/laporan/print', [DashboardController::class, 'laporanPrint'])->name('laporan.penjual.print');
    Route::get('/api/orders/{stall}', [OrderController::class, 'byStall']);
    Route::patch('/api/order/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('/api/foods/{stall}', [FoodController::class, 'byStall']);
    Route::post('/api/food', [FoodController::class, 'store']);
    Route::patch('/api/food/{id}/ready', [FoodController::class, 'toggleReady']);
    Route::delete('/api/food/{id}', [FoodController::class, 'destroy']);
});
