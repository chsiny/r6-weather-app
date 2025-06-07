<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForecastController;

Route::get('/forecast', [ForecastController::class, 'getForecast']);
