<?php

use Illuminate\Support\Facades\Route;
use App\Filament\App\Pages\Auth\Register;
use App\Http\Controllers\KioskController;

Route::post('/kiosk/{token}/check-visitor', [KioskController::class, 'checkVisitor'])->name('kiosk.check');
Route::post('/kiosk/{token}/scan-qr', [KioskController::class, 'scanQr'])->name('kiosk.scan');

Route::group(['middleware' => 'redirect.if.not.installed'], function () {
    Route::get('register', Register::class)
        ->name('filament.app.auth.register')
        ->middleware('signed');
    
    Route::get('/kiosk/{token}', [KioskController::class, 'index'])->name('kiosk.index');
    Route::get('/kiosk/{token}/scan-qr', [KioskController::class, 'showScanQr'])->name('kiosk.scan.form');
    Route::get('/kiosk/{token}/checkin', [KioskController::class, 'showCheckin'])->name('kiosk.checkin.form');
    Route::post('/kiosk/{token}/checkin', [KioskController::class, 'checkin'])->name('kiosk.checkin.post');
});
