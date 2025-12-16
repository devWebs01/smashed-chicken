<?php

use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome-pages.welcome');
});

Route::match(['get', 'post'], '/webhook/whatsapp', WhatsAppController::class);
