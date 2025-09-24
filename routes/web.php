<?php

use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('filament.pages.welcome');
});

Route::match(['get', 'post'], '/webhook/whatsapp', [WhatsAppController::class, 'handleWebhook']);
