<?php

namespace App\Http\Controllers;

use App\Models\AdvertsDelivered;
use App\Models\Customer;
use App\Models\ProviderService;
use App\Models\User; 
use App\Models\AuthOtp; 
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants;
use App\Http\Services\SmsService; 
use App\Http\Repositories\NoticeRepository;

class NoticeController extends BaseController
{  
    private $smsService;
    private $repo; 
    public function __construct(SmsService $smsService, NoticeRepository $repo)
    {
        $this->smsService = $smsService;
        $this->repo = $repo; 
    }
 
 
    /** 
    * Store a newly rate resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function rate(Request $request)
   {
       try {
            $validateData = Validator::make($request->all(), [
                'id_advert'=>'required'  
            ]);

            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

            $user = auth()->user();
            if($user->id_user_type != Constants::USER_TYPE_CLIENT)
                return  $this->sendResponse(null, "Votre profil ne vous permet pas de noter le livreur.", "Your profile does not allow you to rate the delivery person.",400);

            $customer = Customer::where("id_user", $user->id)->first();
            if(!isset($customer)) 
                return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);
             
            if($request->has('rate') && isset($request->rate) && !is_numeric($request->rate)) 
                return  $this->sendResponse(null, "La note doit être un entier", "Rate must be an integer"); 

            if($request->has('rate') && isset($request->rate) && is_numeric($request->rate) && ($request->rate < 1 || $request->rate > 5)) 
                return  $this->sendResponse(null, "La note doit être entre 1 et 5", "The rate must be between 1 and 5"); 
   
            $find = AdvertsDelivered::where(['id_customer' => $customer->id, 'id_advert' => $request->id_advert])-> first();
            if(isset($find)){
                $find->comment = isset($request->comment) ? $request->comment : "null";
                $find->rate  = isset($request->rate) ? $request->rate : 0;
                $find->update();

                $msg = "Livreur noter avec succès";
                return  $this->sendResponse(null, $msg, "Delivery man successfully rated");
            } else{
                $msg = "Une erreur inconnue s'est produite. Re-essayer plus tard";
                return  $this->sendResponse(null, $msg, "AdvertsDelivered not fount");
            }

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
   }

   public function update(Request $request)
   {

        $user = auth()->user();
        if($user->id_user_type != Constants::USER_TYPE_CLIENT)
            return  $this->sendResponse(null, "Votre profil ne vous permet pas de noter le livreur.", "Your profile does not allow you to rate the delivery person.",400);

        $customer = Customer::where("id_user", $user->id)->first();
        if(!isset($customer)) 
            return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);
 
        $validateData = Validator::make($request->all(), [
            'id' => 'required' 
        ]);

        if($validateData->fails())
            return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

        try {   
            $notice = AdvertsDelivered::find($request->id);

            if(!isset($notice)) 
                return  $this->sendResponse(null, "Note non trouvée", "Rate not found", 400);
            
            $updateData = request()->all();  
            $res = AdvertsDelivered::where('id', $request->id)->update($updateData); 
            $notice = AdvertsDelivered::find($request->id);

            $msg = "Informations note mises à jour";
            return  $this->sendResponse($rates, $msg, "Rate informations updated"); 

       } catch (\Throwable $th) {
           //throw $th;
           return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
       }
   }
   
   public function rates(Request $request)
   {

        $user = auth()->user();
        if($user->id_user_type != Constants::USER_TYPE_CLIENT)
            return  $this->sendResponse(null, "Votre profil ne vous permet pas de noter le livreur.", "Your profile does not allow you to rate the delivery person.",400);

        $customer = Customer::where("id_user", $user->id)->first();
        if(!isset($customer)) 
            return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);
 
        try {   
            $rates = AdvertsDelivered::where('id_customer', $customer->id)->select(
                'id', 'rate', 'comment', 'id_customer', 'id_advert', 'id_provider_service',
                //DB::raw("(SELECT COUNT(*) FROM adverts_delivered) AS delivry_count")
                DB::raw("(SELECT COUNT(*) FROM adverts_delivered  WHERE  id_customer = '".$customer->id."') AS delivry_count")
            )->with('provider', function($query){
                $query->select('provider_services.id','id_user', 'users.firstname', 'users.lastname', 'users.profile_photo_path', 'users.email', 'users.phone', 'id_user_type',
                DB::raw("(CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify"),
                DB::raw("(CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify"),
                DB::raw("(CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify"), 'email', 'users.created_at AS user_registration_date')
                ->join('users', 'users.id', '=', 'id_user');
            })->get();
             
            $msg = "Toutes les notes";
            return  $this->sendResponse($rates, $msg, "All rates");

       } catch (\Throwable $th) {
           //throw $th;
           return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
       }
   }


   public function ratesByProvider(Request $request)
   {

        $user = auth()->user();
        if($user->id_user_type != Constants::USER_TYPE_CLIENT)
            return  $this->sendResponse(null, "Votre profil ne vous permet pas de noter le livreur.", "Your profile does not allow you to rate the delivery person.",400);

        $customer = Customer::where("id_user", $user->id)->first();
        if(!isset($customer)) 
            return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);

        $validateData = Validator::make($request->all(), [
            'id_provider'=>'required' 
        ]);

        if($validateData->fails())
            return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

        try {  
            if($request->has('id_provider') && isset($request->id_provider) && !is_numeric($request->id_provider)) 
                return  $this->sendResponse(null, "id_provider doit être un entier", "id_provider must be an integer"); 
 
            $id_provider = $request->id_provider;
            $rates = AdvertsDelivered::where('id_provider_service', $request->id_provider)->select(
                'id', 'rate', 'comment', 'id_customer', 'id_advert', 'id_provider_service',
                DB::raw("(SELECT COUNT(*) FROM adverts_delivered WHERE id_provider_service = '".$id_provider."') AS delivry_count")
            )->with('customer', function($query) {
                $query->select('customers.id','id_user', 'users.firstname', 'users.lastname', 'users.profile_photo_path', 'users.email', 'users.phone', 
                DB::raw("(CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify"),
                DB::raw("(CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify"),
                DB::raw("(CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify"), 'email', 'users.created_at AS user_registration_date')
                ->join('users', 'users.id', '=', 'id_user');
            })->get();
             
            $msg = "Toutes les notes";
            return  $this->sendResponse($rates, $msg, "All rates");

       } catch (\Throwable $th) {
           //throw $th;
           return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
       }
   }
}
