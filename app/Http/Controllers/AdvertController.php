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
            $customer = User::find($user->id);
            
            $validateData = Validator::make($request->all(), [
                'departure_city'=>'required',
                'arrival_city'=>'required', 
                'name'=>'required',
                'nature_package'=>'required'
            ]);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

            $advert                 = new Advert();

            Advert::create([
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
            return  $this->sendResponse(null, $msg, "advert created successfully");

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

                $allAdvert = Advert::where("id_customer", $customer->id)->get();
                $msg = "Tous les annonces";

                return  $this->sendResponse($allAdvert, $msg, "All advertisements");
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
 
        $msg = "Service en cours de developpement";
        return  $this->sendResponse(null, $msg, "Service under development");
       /* try {
            $user = auth()->user();
            $customer = User::find($user->id);
            
            $request->validate([
                'departure_city'=>'required',
                'arrival_city'=>'required', 
                'name'=>'required',
                'nature_package'=>'required'
            ]);
    
            $advert                 = new Advert();
            $advert->name           = $request->name;
            $advert->departure_city = $request->departure_city;
            $advert->arrival_city   = $request->departure_city;
            $advert->description    = $request->description;
            $advert->nature_package = $request->nature_package;
            $advert->id_customer    = $customer->id;

            $advert->save();
            $msg = "Annonce créée avec succès";
            return  $this->sendResponse(null, $msg, "advert created successfully");

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        } */
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
