<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StateMachineController;

Route::get('/', [StateMachineController::class, 'index']);
Route::post('/modthree', [StateMachineController::class, 'actionCalculate']);
