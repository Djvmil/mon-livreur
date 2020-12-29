<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\AdvertResponse;
use App\Models\User;
use App\Models\Customer;
use App\Models\ProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants;
use App\Http\Service\SendSms;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Validator; 

class AdvertController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
            $user = auth()->user();
            $customer = Customer::where("id_user", $user->id)->first();
            
            $validateData = Validator::make($request->all(), [
                'departure_city'=>'required',
                'arrival_city'=>'required', 
                'name'=>'required',
                'nature_package'=>'required'
            ]);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

            $advert   =Advert::create([
                'name' => $request->name,
                'departure_city' => $request->departure_city,
                'arrival_city' => $request->departure_city,
                'departure_date' => isset($request->departure_date) ? $request->departure_date : "null",
                'acceptance_date' => isset($request->departure_date) ? $request->departure_date : "null",
                'description' => isset($request->description) ? $request->description : "null",
                'nature_package' => $request->nature_package,
                'contact_person_name' => isset($request->contact_person_name) ? $request->contact_person_name : "null",
                'contact_person_phone' =>isset($request->contact_person_phone) ? $request->contact_person_phone : "null",
                'id_customer' => $customer->id
            ]); 

            $msg = "Annonce créée avec succès";
            return  $this->sendResponse(null, $advert, "advert created successfully");

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }

 
    public function allAdvert(Request $request)
    {
        try {
            $user = auth()->user();
            if($user->id_user_type == Constants::USER_TYPE_ADMIN){
                $provider = ProviderService::where("id_user", $user->id)->first();

                //$allAdvert = Advert::where("id_customer", $provider->id)->get(); 

                $msg = "Service en cours de developpement";
                return  $this->sendResponse(null, $msg, "Service under development");

            } else if($user->id_user_type  == Constants::USER_TYPE_CLIENT ){
                $customer = Customer::where("id_user", $user->id)->first(); 
                
                /* $queryAdverts = DB::table('adverts')
                ->select('id', 'departure_city', 'arrival_city', 'state',
                        'acceptance_date', 'departure_date', 'created_at', 'updated_at')
                ->where('id_customer', $customer->id)
                ->get(); */ 

                //$per_page = request('per_page') != null ? request('per_page') : Constants::DEFAULT_PER_PAGE;
                //$allAdvert = Advert::where("id_customer", $customer->id)->latest()->paginate($per_page); 

                //$allAdvert = Advert::where("id_customer", $customer->id)->get(); 
                
                $queryAdverts = "SELECT id, departure_city, arrival_city, state,
                                    acceptance_date, departure_date, 
                                    (CASE WHEN taken = 0 THEN 'false' ELSE 'true' END) AS taken, price, nature_package, 
                                    (SELECT COUNT(*) FROM advert_responses WHERE id_advert = adverts.id ) as provider_response_count, created_at, updated_at
                                    FROM adverts
                            WHERE id_customer = '".$customer->id."'";

                $resultAdverts = DB::SELECT(DB::RAW($queryAdverts));  


                if(isset($resultAdverts) && count($resultAdverts) > 0){
                    $msg = "Tous les annonces";
                    $debugMsg = "All advertisements";
                }else{
                    $msg = "Vous n'avez pas d'annonce";
                    $debugMsg = "You don't have any advertising!";

                }

                return  $this->sendResponse($resultAdverts, $msg, $debugMsg);
            } else{

                $msg = "Service en cours de developpement";
                return  $this->sendResponse(null, $msg, "Service under development");

            }
 

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        } 
    }

 
    public function advertById($id)
    {
        try {
            $user = auth()->user();  

            $advert = Advert::find($id);
              
            $msg = "Informations annonce ";
            return  $this->sendResponse($advert, $msg, "Advert informations");

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        } 
    }
 
 
    public function applyOnAdvert(Request $request)
    { 
        try {
            $user = auth()->user();
            $provider = ProviderService::find($user->id);

            $validateData = Validator::make($request->all(), [
                'id_advert'=>'required' 
            ]);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);


            if(!isset($provider)){
                if($user->id_user_type != Constants::USER_TYPE_PRESTATAIRE){
                    $msg = "Avec votre profil, vous ne pouvez pas potuler sur une annonce ";
                    $debugMsg = "";
                    return  $this->sendResponse(null, $msg, $debugMsg);
                }
            }

            $applyData = request()->all();   
            $applyData['id_provider_service'] = $provider->id;
            $applyData['taken'] = isset($request->taken) ? $request->taken : false;
            $applyData['price'] = isset($request->price) ? $request->price : 0; 
            $applyData['comment'] = isset($request->comment) ? $request->comment : "null";
            $applyData['acceptance_date'] = isset($request->acceptance_date) ? $request->acceptance_date : "null";
 
            $apply = AdvertResponse::create($applyData);  
            
            $msg = "Vous avez postuler avec succès sur l'annonce";
            return  $this->sendResponse(null, $msg, "");

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Advert  $advert
     * @return \Illuminate\Http\Response
     */
    public function  update(Request $request){
        try {
            $user = auth()->user(); 
            $customer   = User::find($user->id);

            $updateData = request()->all();
 
            $res = Advert::where("id", $request->id)->update($updateData); 

            return  $this->sendResponse(Advert::find($request->id), "Informations annonce mises à jour", "Advert informations updated"); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
     }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Advert  $advert
     * @return \Illuminate\Http\Response
     */
    public function show(Advert $advert)
    {
        //
    }
 

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Advert  $advert
     * @return \Illuminate\Http\Response
     */
    public function destroy(Advert $advert)
    {
        //
    }
}
