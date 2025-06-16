<?php

use App\Http\Controllers\RentItemPosterController;
use App\Http\Controllers\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/get-user/{id}', [\App\Http\Controllers\AuthController::class, 'getUser']);

Route::post('/add-item-poster', [RentItemPosterController::class, 'store']);
Route::get('/item-posters', [RentItemPosterController::class, 'index']);
Route::delete('/item-posters/{id}', [RentItemPosterController::class, 'destroy']);

Route::post('/add-item', [\App\Http\Controllers\RentItemController::class, 'store']);
Route::get('/items', [\App\Http\Controllers\RentItemController::class, 'index']);
Route::delete('/items/{id}', [\App\Http\Controllers\RentItemController::class, 'destroy']);

Route::post('/add-item-with-poster', [\App\Http\Controllers\RentItemController::class, 'storeWithPoster']);
Route::get('get-item/{id}', [\App\Http\Controllers\RentItemController::class, 'show']);

Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/rent_items/{rent_item_id}/available-dates', [ReservationController::class, 'availableDates']);
Route::get('/rent_items/{rent_item}/booked-dates', [ReservationController::class, 'bookedDates']);
Route::patch('/reservations/{id}/confirm', [ReservationController::class, 'confirm']);
Route::patch('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
Route::get('/rent_items/{rent_item_id}/reservations/pending', [ReservationController::class, 'pendingReservationsForRentItem']);
