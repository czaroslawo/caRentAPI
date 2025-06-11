<?php

use App\Http\Controllers\RentItemPosterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/add-item-poster', [RentItemPosterController::class, 'store']);
Route::get('/item-posters', [RentItemPosterController::class, 'index']);
Route::delete('/item-posters/{id}', [RentItemPosterController::class, 'destroy']);
Route::post('/add-item', [\App\Http\Controllers\RentItemController::class, 'store']);
Route::get('/items', [\App\Http\Controllers\RentItemController::class, 'index']);
Route::delete('/items/{id}', [\App\Http\Controllers\RentItemController::class, 'destroy']);
Route::post('/add-item-with-poster', [\App\Http\Controllers\RentItemController::class, 'storeWithPoster']);
Route::get('get-item/{id}', [\App\Http\Controllers\RentItemController::class, 'show']);
Route::get('/get-user/{id}', [\App\Http\Controllers\AuthController::class, 'getUser']);
