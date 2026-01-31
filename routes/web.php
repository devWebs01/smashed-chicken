<?php

use App\Http\Controllers\AutoReplyController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome-pages.welcome');
});

// Fonnte Webhook - Accept GET and POST
// Route::match(['get', 'post'], '/webhook/whatsapp', WhatsAppController::class);
Route::match(['get', 'post'], '/webhook/whatsapp', WhatsAppController::class)->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Auto-Reply Webhook - Accept GET and POST, reply to any message automatically
Route::match(['get', 'post'], '/webhook/autoreply', AutoReplyController::class)->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
