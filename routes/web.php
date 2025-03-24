<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/leaflet', [MapController::class, 'leafletMap']);
Route::get('/mapbox', [MapController::class, 'mapboxMap']);
Route::get('/api/wiup', [MapController::class, 'getWiup']);
Route::get('/api/bataslaut', [MapController::class, 'getBatasLaut']);
Route::get('/api/wilayahpertambangan', [MapController::class, 'getWilayahPertambangan']);
