<?php

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

Route::middleware('auth:api')->get('/admin', function (Request $request) {
    return $request->user();
});

Route::apiResource('client', \App\Http\Controllers\ClientController::class);
Route::apiResource('annonce',\App\Http\Controllers\AnnonceController::class);
Route::apiResource('prestataire',\App\Http\Controllers\PrestataireController::class);

//route register and login client
Route::post('/clientregister', 'App\Http\Controllers\ClientController@register');
Route::post('/clientlogin', 'App\Http\Controllers\ClientController@login');

//route register and login prestataire
Route::post('/prestataireregister', 'App\Http\Controllers\PrestataireController@register');
Route::post('/prestatairelogin', 'App\Http\Controllers\PrestataireController@login');

