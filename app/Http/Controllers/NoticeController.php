<?php

namespace App\Http\Controllers;

use App\Models\Notice;
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
use App\Http\Service\SmsService; 
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
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function create(Request $request)
   {
       try {
            $validateData = Validator::make($request->all(), [
                'id_provider'=>'required' 
            ]);

            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

            $user = auth()->user();
            if($user->id_user_type != Constants::USER_TYPE_CLIENT)
                return  $this->sendResponse(null, "Votre profil ne vous permet pas de noter le livreur.", "Your profile does not allow you to rate the delivery person.",400);

            $customer = Customer::where("id_user", $user->id)->first();
             
            if($request->has('rate') && isset($request->rate) && !is_numeric($request->rate)) 
                return  $this->sendResponse(null, "La note doit être un entier", "Rate must be an integer"); 

            if($request->has('rate') && isset($request->rate) && is_numeric($request->rate) && ($request->rate < 1 || $request->rate > 5)) 
                return  $this->sendResponse(null, "La note doit être entre 1 et 5", "The rate must be between 1 and 5"); 
   
            $advert   = Notice::create([ 
                'comment' => isset($request->comment) ? $request->comment : "null", 
                'rate' => isset($request->rate) ? $request->rate : -1, 
                'id_provider' => $request->id_provider,
                'id_advert' => $request->id_advert,
                'id_customer' => $customer->id
            ]); 

           $msg = "Livreur noter avec succès";
           return  $this->sendResponse(null, $msg, "Delivery man successfully rated");

       } catch (\Throwable $th) {
           //throw $th;
           return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
       }
   }

   public function update(Request $request)
   {
        $validateData = Validator::make($request->all(), [
            'id'=>'required',
            'id_advert'=>'required',
            'id_provider'=>'required',
            'id_customer'=>'required' 
        ]);

        if($validateData->fails())
            return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);


        try {   
            $notice = Notice::find($request->id);

            if(!isset($notice)) 
                return  $this->sendResponse(null, "Note non trouvée", "Rate not found", 400);
            
            $updateData = request()->all();  

            $res = Notice::where('id', $request->id)->update($updateData); 
            $notice = Notice::find($request->id);

            $msg = "Informations note mises à jour";
            return  $this->sendResponse($rates, $msg, "Rate informations updated"); 

       } catch (\Throwable $th) {
           //throw $th;
           return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
       }
   }
   
   public function rates(Request $request)
   {
        $validateData = Validator::make($request->all(), [
            'id_provider'=>'required' 
        ]);

        if($validateData->fails())
            return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);


        try {  
            if($request->has('id_provider') && isset($request->id_provider) && !is_numeric($request->id_provider)) 
                return  $this->sendResponse(null, "id_provider doit être un entier", "id_provider must be an integer"); 
 
            $id_provider = $request->id_provider;
            $rates = Notice::where('id_provider', $request->id_provider)->select(
                'id', 'rate', 'comment', 'id_customer', 'id_advert', 'id_provider',
                DB::raw("(SELECT COUNT(*) FROM advert_responses WHERE id_provider_service = '".$id_provider."') AS delivry_count")
            )->with('customer', function($query) use ($id_provider){
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
