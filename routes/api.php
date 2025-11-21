<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;
use \App\Http\Controllers\SearchController;

Route::get('/', [AddressController::class, 'index'])->name('address.index');
Route::post('/search', [AddressController::class, 'search'])->name('address.search');

Route::get('/test-manticore', [SearchController::class, 'testManticore'])->name('manticore.test');
