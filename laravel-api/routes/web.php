<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelController;



// Panel de control ESP32
Route::get('/', [PanelController::class, 'index']);
Route::post('/', [PanelController::class, 'update']);
