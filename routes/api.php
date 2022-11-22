<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DureeController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServicePersonnelController;
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
// Fonctionnalités temporaires, seront à transformer pour utiliser le package sanctum de laravel lors du srpint 2
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::post('/auth/register', [AuthController::class, 'createUser']);
//
Route::apiResource('durees', DureeController::class);
Route::apiResource('servicespersonnels', ServicePersonnelController::class);
Route::apiResource('rendezvous', RendezVousController::class); //->middleware('auth:sanctum');
Route::apiResource('client', ClientController::class); //->middleware('auth:sanctum');
//
/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
