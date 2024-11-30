<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, "index"]);

Route::match(['get', 'post'], '/search', [SearchController::class, 'search'])->name('search.search');
