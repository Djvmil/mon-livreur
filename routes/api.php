<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrestataireController;
use App\Http\Controllers\ClientController;

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

Route::middleware('auth:sanctum')->get('/admin', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::get('allclient', [ClientController::class, 'allClient']);
    Route::get('allprestataire', [PrestataireController::class, 'allPrestataire']);
});


Route::apiResource('annonce',\App\Http\Controllers\AnnonceController::class);

//route register and login client
Route::post('clientregister', [ClientController::class, 'register']);
Route::post('/clientlogin', [ClientController::class, 'login']);
Route::get('{provider}', [ClientController::class, 'redirectToProvider'])->where('provider', 'facebook|google');
Route::get('{provider}/callback', [ClientController::class, 'handleProviderCallback'])->where('provider', 'facebook|google');


//route register and login prestataire
Route::post('/prestataireregister', [PrestataireController::class, 'register']);
Route::post('/prestatairelogin', [PrestataireController::class, 'login']);
Route::get('/{provider}', [PrestataireController::class, 'redirectToProvider'])->where('provider', 'facebook|google');
Route::get('/{provider}/callback', [PrestataireController::class, 'handleProviderCallback'])->where('provider', 'facebook|google');


