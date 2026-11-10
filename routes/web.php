<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/report', [ReportController::class, 'index']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/search', [EventController::class, 'search']);
