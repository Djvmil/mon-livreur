<?php
namespace App\Http\Repositories; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Advert;
use App\Models\AdvertsDelivered;
use Carbon\Carbon;

class AdvertRepository extends BaseRepository
{ 
    function __construct(){
		$this->model = new Advert();
	}

	function finalizationOfDelivery($advert, $advertResponse){
  
		$applyData = array();    
		$applyData['price'] = isset($advertResponse->price) ? $advertResponse->price : 0; 
		$applyData['comment'] = isset($advert->comment) ? $advert->comment : "null";
		$applyData['rate'] = isset($advert->rate) ? $advert->rate : 0;
		$applyData['taken'] = true;
		$applyData['delivered_date'] = Carbon::now();
		$applyData['id_advert'] = $advert->id;
		$applyData['id_provider_service'] = $advertResponse->id_provider_service;
		$applyData['id_customer'] = $advert->id_customer;
		$applyData['id_advert_response'] = $advertResponse->id;

		$find = AdvertsDelivered::where(['id_customer' => $advert->id_customer, 'id_advert' => $advert->id])-> first();
		if(isset($find))
			return $find->update($applyData);

		return AdvertsDelivered::create($applyData);
	}
}