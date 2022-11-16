<?php

use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UtilisateurController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::apiResource('services', ServiceController::class);
Route::apiResource('utilisateurs', UtilisateurController::class);
//
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
