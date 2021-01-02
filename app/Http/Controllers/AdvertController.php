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
                'arrival_city' => $request->arrival_city,
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

                //$allAdvert = Advert::where("id_customer", $provider->id)->get(); 

                $msg = "Service en cours de developpement";
                return  $this->sendResponse(null, $msg, "Service under development");

            } else if($user->id_user_type  == Constants::USER_TYPE_CLIENT ){
                $customer = Customer::where("id_user", $user->id)->first(); 


                if(!isset($customer)) 
                return  $this->sendResponse(null, "Client non trouvée", "Customer not found"); 
                    
                /* $queryAdverts = DB::table('adverts')
                ->select('id', 'departure_city', 'arrival_city', 'state',
                        'acceptance_date', 'departure_date', 'created_at', 'updated_at')
                ->where('id_customer', $customer->id)
                ->get(); */ 

                //$per_page = request('per_page') != null ? request('per_page') : Constants::DEFAULT_PER_PAGE;
                //$allAdvert = Advert::where("id_customer", $customer->id)->latest()->paginate($per_page); 

                //$allAdvert = Advert::where("id_customer", $customer->id)->get(); 
                
                $queryAdverts = "SELECT adverts.id, adverts.name, adverts.description, departure_city, arrival_city, adverts.state,
                                        acceptance_date, departure_date, 
                                        (CASE WHEN taken = 0 THEN 'false' ELSE 'true' END) AS taken, price, nature_package, 
                                        (SELECT COUNT(*) FROM advert_responses WHERE id_advert = adverts.id) as provider_response_count, created_at, updated_at
                                    FROM adverts
                                    WHERE id_customer = '".$customer->id."' 
                                    AND deleted_at is null ";

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

                $provider = ProviderService::where("id_user", $user->id)->first();
                if(!isset($provider)) 
                    return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found"); 
 
                $queryAdverts = "SELECT adverts.id, id_user, adverts.name, adverts.description, adverts.departure_city, adverts.arrival_city, adverts.state,
                                        adverts.acceptance_date, adverts.departure_date, users.firstname, users.lastname, users.profile_photo_path,
                                        (CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify,
                                        (CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify,
                                        (CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify, 
                                        users.created_at AS user_registration_date,
                                        (CASE WHEN taken = 0 THEN 'false' ELSE 'true' END) AS taken, adverts.price, adverts.nature_package, 
                                        (SELECT COUNT(*) FROM advert_responses WHERE id_advert = adverts.id) AS provider_response_count, adverts.created_at, adverts.updated_at
                                    FROM adverts, customers, users 
                                    WHERE adverts.id_customer = customers.id 
                                    AND customers.id_user = users.id 
                                    AND adverts.taken is false  
                                    AND adverts.deleted_at IS NULL
                                    AND adverts.id 
                                        NOT IN (SELECT advert_responses.id_advert 
                                                FROM advert_responses 
                                                WHERE advert_responses.id_provider_service = '".$provider->id."' 
                                                AND advert_responses.id_advert = adverts.id )";

                $resultAdverts = DB::SELECT(DB::RAW($queryAdverts));  


                if(isset($resultAdverts) && count($resultAdverts) > 0){
                    $msg = "Tous les annonces";
                    $debugMsg = "All advertisements";
                }else{
                    $msg = "Il n'y a pas d'annonce disponible";
                    $debugMsg = "There is no advert available!";
                }

                return  $this->sendResponse($resultAdverts, $msg, $debugMsg); 

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
            $customer  = Customer::where("id_user", $user->id)->first();  
/*
            $advert    = null;
            if($user->id_user_type == 2) {
                $advert = Advert::where(["id" => $id, "id_customer" => $customer->id]); 
            }
            else
                $advert = Advert::find($id);

            if($customer->id != $advert->id_customer){
                $msg = "Informations annonce ";
                return  $this->sendResponse($advert, $msg, "Advert informations");

            }
*/
              
            $advert = Advert::find($id);

            if(!isset($advert)) 
                return  $this->sendResponse(null, "Annonce non trouvée", "Advert not found"); 
                
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
            $provider = ProviderService::where("id_user", $user->id)->first(); 

            $validateData = Validator::make($request->all(), [
                'id_advert'=>'required' 
            ]);

            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found"); 
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
 
            if(!isset($provider)){
                if($user->id_user_type != Constants::USER_TYPE_PRESTATAIRE){
                    $msg = "Avec votre profil, vous ne pouvez pas potuler sur une annonce";
                    $debugMsg = "With your profile, you cannot potulate on an advert";
                    return  $this->sendResponse(null, $msg, $debugMsg);
                }
            }

            $applyData = request()->all();   
            $applyData['id_provider_service'] = $provider->id;
            $applyData['taken'] = false;
            $applyData['price'] = isset($request->price) ? $request->price : 0; 
            $applyData['comment'] = isset($request->comment) ? $request->comment : "null";
            $applyData['acceptance_date'] = "null";
 
            $apply = AdvertResponse::create($applyData);  
            
            $msg = "Vous avez postuler avec succès sur l'annonce";
            return  $this->sendResponse(null, $msg, "You have successfully applied on the advert");

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
            $providerOrProvider = null;
            if($user->id_user_type == Constants::USER_TYPE_PRESTATAIRE){

                $providerOrProvider   = ProviderService::where("id_user", $user->id)->first(); 

            if(!isset($providerOrProvider)) 
                    return  $this->sendResponse(null, "Prestataire non trouvée", "Provider not found"); 
 
                $msg = "Service en cours de developpement";
                return  $this->sendResponse(null, $msg, "Service under development");

            }else{
                $providerOrProvider   = Customer::where("id_user", $user->id)->first(); 
                if(!isset($providerOrProvider)) 
                    return  $this->sendResponse(null, "Client non trouvée", "Customer not found"); 
            }
            
            $validateData = Validator::make($request->all(), [
                'id'=>'required' 
            ]);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
 
            $advert = Advert::find($request->id);

            if(!isset($advert)) 
                return  $this->sendResponse(null, "Annonce non trouvée", "Advert not found"); 
            
            $updateData = request()->all(); 
  
            if($request->has("state") && !isset($request->state)) 
                $updateData["state"] = $advert->state; 

            if($request->has("state") && isset($request->state) && $request->state == Constants::TAKEN_STATE){
                $updateData["taken"] = true;     
            }

            $res = Advert::where(["id" => $request->id, "id_customer" => $providerOrProvider->id])->update($updateData); 
            $advert = Advert::find($request->id);
            
            if($res == 0 && $providerOrProvider->id != $advert->id_customer) 
                return  $this->sendResponse(null, "Vous ne pouvez modifier que les annonces que vous avez publié", "You can only edit the adverts you posted"); 
 
            if($advert->state == Constants::DELETED_STATE){
                $advert->delete();
                return  $this->sendResponse(null, "Annonce supprimée avec succès", "Advert deleted"); 
            }

            return  $this->sendResponse($advert, "Informations annonce mises à jour", "Advert informations updated"); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
    }


 
    public function advertsByProvider(Request $request)
    {
        try {
            $user = auth()->user();
            $provider = ProviderService::where("id_user", $user->id)->first();
            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found"); 
                
            $provider = ProviderService::where("id_user", $user->id)->first();  
 
            $queryAdverts = "SELECT adverts.id, users.id AS id_user, adverts.name, adverts.description, adverts.departure_city, adverts.arrival_city, 
                                    adverts.acceptance_date, adverts.departure_date, users.firstname, users.lastname, users.profile_photo_path,
                                    (CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify,
                                    (CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify,
                                    (CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify, adverts.state,
                                    (CASE WHEN adverts.taken = 1 AND advert_responses.taken = 1 THEN '".Constants::ACCEPTED_STATUS."' 
                                          WHEN adverts.taken = 1 AND advert_responses.taken = 0 THEN '".Constants::REFUSED_STATUS."' 
                                           ELSE '".Constants::WAITING_STATUS."' END) AS status,
                                    users.created_at AS user_registration_date,
                                    (CASE WHEN adverts.taken = 0 THEN 'false' ELSE 'true' END) AS taken, adverts.price, adverts.nature_package, 
                                    (SELECT COUNT(*) FROM advert_responses WHERE id_advert = adverts.id) AS provider_response_count, adverts.created_at, adverts.updated_at 
                                FROM adverts, customers, users, advert_responses
                                WHERE adverts.id_customer = customers.id 
                                AND customers.id_user = users.id 
                                AND advert_responses.id_provider_service = '".$provider->id."' 
                                AND adverts.deleted_at IS NULL";

            $resultAdverts = DB::SELECT(DB::RAW($queryAdverts));  
            
            if(isset($resultAdverts) && count($resultAdverts) > 0){
                $msg = "Tous les annonces";
                $debugMsg = "All advertisements";
            }else{
                $msg = "Il n'y a pas d'annonce disponible";
                $debugMsg = "There is no advert available!";
            }

            return  $this->sendResponse($resultAdverts, $msg, $debugMsg); 
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
