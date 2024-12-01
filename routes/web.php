<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, "index"]);

Route::match(['get', 'post'], '/search', [ImageController::class, 'search'])->name('image.search');
