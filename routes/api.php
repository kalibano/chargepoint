<?php
use App\Http\Controllers\ChargingSessionController;
use Illuminate\Support\Facades\Route;

Route::post('/charging-session', [ChargingSessionController::class, 'start']);
Route::get('/', function(){
    return "try";
});
