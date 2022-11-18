<?php

use App\Http\Controllers\Api\DureeController;
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

// ->only (permet de spécifier les méthodes autorisées par cette route qui se trouve dans la classe controller correspondante)
Route::apiResource('services', ServiceController::class)->only([
    'index', 'show'
]);
Route::apiResource('utilisateurs', UtilisateurController::class);
Route::apiResource('durees', DureeController::class);
//
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
