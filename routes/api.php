<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\AdvertController;


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


Route::post('register', [RegisterController::class, 'register']);
Route::post('login',  [LoginController::class, 'login']);

Route::post('check',  [UserController::class, 'check']);
Route::post('otp-confirmation', [UserController::class, 'otpConfirmation']);

Route::group(['middleware' => 'auth:sanctum'], function() {
    //Auth
    Route::post('logout',  [LoginController::class, 'logout']);
    Route::get('get-profile',  [UserController::class, 'show']);
    Route::post('update-profile',  [UserController::class, 'update']); 

    //Advert
    Route::post('create-advert', [AdvertController::class, 'create']);
    Route::post('update-advert', [AdvertController::class, 'update']);
    Route::post('apply-on-advert', [AdvertController::class, 'applyOnAdvert']);
    Route::get('adverts/{id}', [AdvertController::class, 'advertById']);
    Route::get('adverts', [AdvertController::class, 'adverts']);
    Route::get('adverts-except-postulated', [AdvertController::class, 'advertsExceptPostulated']);
    Route::get('adverts-postulated', [AdvertController::class, 'advertsPostulated']);
    Route::get('adverts-by-provider', [AdvertController::class, 'advertsByProvider']);
    Route::get('providers-by-advert/{id}', [AdvertController::class, 'providersByAdvert']);
    Route::post('choose-this-provider', [AdvertController::class, 'chooseThisProvider']);


    Route::get('adverts-with-providers-status', [AdvertController::class, 'advertsWithProviderStatus']);
    Route::post('change-state-advert', [AdvertController::class, 'changeStateAdvert']);
});


/*
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


*/
