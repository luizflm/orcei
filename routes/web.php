<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/locale/{locale}', LocaleController::class)->name('set-locale');
