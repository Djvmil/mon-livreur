<?php

namespace App\Http\Controllers;

use App\Models\AdvertsDelivered;
use App\Models\Advert;
use App\Models\AdvertResponse;
use App\Models\User;
use App\Models\Customer;
use App\Models\ProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants;
use App\Enums\StateAdvert; 
use Illuminate\Support\Str; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator; 
use App\Http\Services\SmsService;
use App\Http\Repositories\AdvertRepository;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AdvertController extends BaseController
{
    private $smsService;
    private $repo; 
    public function __construct(SmsService $smsService, AdvertRepository $repo, Messaging $messaging)
    {
        $this->smsService = $smsService;
        $this->repo = $repo; 
        $this->messaging = $messaging;
    }

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
            if($user->id_user_type != Constants::USER_TYPE_CLIENT)
                return  $this->sendResponse(null, "Votre profil ne vous permet pas de creer une annonce.", "Your profile does not allow you to create an advert.",400);
 
            $customer = Customer::where("id_user", $user->id)->first();
            
            $validateData = Validator::make($request->all(), [
                'departure_city'=>'required',
                'arrival_city'=>'required', 
                'name'=>'required',
                'nature_package'=>'required'
            ]);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

            $advert   = Advert::create([
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


            try{ 
                $message = CloudMessage::withTarget('topic', Constants::TOPICS_PROVIDER_SERVICE)
                ->withNotification(Notification::create("Offre de livraison", "Un colis à livrer est disponible")) 
                ->withData(['type' => 'type_1']);
                $this->messaging->send($message);  
            }catch(\Throwable $ex){
                $ex->getMessage();
                
            }

            $msg = "Annonce créée avec succès";
            return  $this->sendResponse(null, $msg, "advert created successfully");

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }

    /**
     * Client: Tous les annonces d'un client
     * 
     * Prestataire: Tous les annonces avec comme status: POSTULED | NOT_POSTULED
     * 
     */
    public function adverts(Request $request)
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
                                        (SELECT COUNT(*) FROM advert_responses WHERE advert_responses.id_advert = adverts.id) as provider_response_count, created_at, updated_at
                                    FROM adverts
                                    WHERE id_customer = '".$customer->id."' 
                                    AND deleted_at is NULL ";

                if($request->has('state') && isset($request->state) && !is_numeric($request->state)) 
                    return  $this->sendResponse(null, "Le state doit être un entier", "State must be an integer"); 

                if($request->has('state') && isset($request->state) && is_numeric($request->state) && ($request->state < 1 || $request->state > 10)) 
                    return  $this->sendResponse(null, "Le state doit être entre 1 et 10", "The internship must be between 1 and 10"); 

                if($request->has('state') && isset($request->state))
                    $queryAdverts = $queryAdverts . " AND adverts.state = '".StateAdvert::map()[$request->state]."' ";

                if($request->has('arrival_city') && !isset($request->arrival_city) ) 
                    return  $this->sendResponse(null, "Le L'adresse de d'arrivée ne doit pas être null", "Arrival city must be not null");  

                if($request->has('arrival_city') && isset($request->arrival_city))
                    $queryAdverts = $queryAdverts . " AND adverts.arrival_city LIKE '%".$request->arrival_city."%' ";
            
                if($request->has('departure_city') && !isset($request->departure_city) ) 
                    return  $this->sendResponse(null, "L'adresse de depart ne doit pas être null", "Departure_city must be not null");  

                if($request->has('departure_city') && isset($request->departure_city))
                    $queryAdverts = $queryAdverts . " AND adverts.departure_city LIKE '%".$request->departure_city."%' ";


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
                                        (CASE WHEN 
                                            (SELECT id FROM advert_responses WHERE id_advert = adverts.id AND advert_responses.id_provider_service = '".$provider->id."') 
                                            IS NOT NULL THEN 'POSTULED' ELSE 'NOT_POSTULED' END) AS status,
                                        adverts.acceptance_date, adverts.departure_date, users.firstname, users.lastname, users.profile_photo_path, users.phone, users.email,
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
                                    AND adverts.deleted_at IS NULL";
              
                if($request->has('state') && isset($request->state) && !is_numeric($request->state)) 
                    return  $this->sendResponse(null, "Le state doit être un entier", "State must be an integer"); 

                if($request->has('state') && isset($request->state) && is_numeric($request->state) && ($request->state < 1 || $request->state > 10)) 
                    return  $this->sendResponse(null, "Le state doit être entre 1 et 10", "The internship must be between 1 and 10"); 

                if($request->has('state') && isset($request->state))
                    $queryAdverts = $queryAdverts . " AND adverts.state = '".StateAdvert::map()[$request->state]."' ";
          
                if($request->has('arrival_city') && !isset($request->arrival_city) ) 
                    return  $this->sendResponse(null, "Le L'adresse de d'arrivée ne doit pas être null", "Arrival city must be not null");  

                if($request->has('arrival_city') && isset($request->arrival_city))
                    $queryAdverts = $queryAdverts . " AND adverts.arrival_city LIKE '%".$request->arrival_city."%' ";
            
                if($request->has('departure_city') && !isset($request->departure_city) ) 
                    return  $this->sendResponse(null, "L'adresse de depart ne doit pas être null", "Departure_city must be not null");  

                if($request->has('departure_city') && isset($request->departure_city))
                    $queryAdverts = $queryAdverts . " AND adverts.departure_city LIKE '%".$request->departure_city."%' ";

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


    /**
     * Prestataire: Tous les annonces sauf les annonces déjà postulé avec comme status: POSTULED
     * 
     */
    public function advertsExceptPostulated(Request $request)
    {
        try {
            $user = auth()->user();
            $provider = ProviderService::where("id_user", $user->id)->first();
            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found"); 

            $queryAdverts = "SELECT adverts.id, id_user, adverts.name, adverts.description, adverts.departure_city, adverts.arrival_city, adverts.state,
                                        (CASE WHEN 
                                            (SELECT id FROM advert_responses WHERE id_advert = adverts.id AND advert_responses.id_provider_service = '".$provider->id."') 
                                            IS NOT NULL THEN 'POSTULED' ELSE 'NOT_POSTULED' END) AS status,
                                    adverts.acceptance_date, adverts.departure_date, users.firstname, users.lastname, users.profile_photo_path, users.phone, users.email,
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
                                AND adverts.state != 'Delivered'  
                                AND adverts.deleted_at IS NULL
                                AND adverts.id 
                                    NOT IN (SELECT advert_responses.id_advert 
                                            FROM advert_responses 
                                            WHERE advert_responses.id_provider_service = '".$provider->id."' 
                                            AND advert_responses.id_advert = adverts.id )";

            if($request->has('state') && isset($request->state) && !is_numeric($request->state)) 
                return  $this->sendResponse(null, "Le state doit être un entier", "State must be an integer"); 

            if($request->has('state') && isset($request->state) && is_numeric($request->state) && ($request->state < 1 || $request->state > 10)) 
                return  $this->sendResponse(null, "Le state doit être entre 1 et 10", "The internship must be between 1 and 10"); 

                

            if($request->has('state') && isset($request->state))
                $queryAdverts = $queryAdverts . " AND adverts.state = '".StateAdvert::map()[$request->state]."' ";

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
     * Prestataire: Tous les annonces avec comme status: POSTULED, 
     * 
     */
    public function advertsPostulated(Request $request)
    {
        try {
            $user = auth()->user();
            $provider = ProviderService::where("id_user", $user->id)->first();
            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found"); 


                // $val = AdvertsDelivered::where('id_provider_service', $item->provider->id)->select(
                //     DB::raw("SUM(rate) as rate"),
                //     DB::raw("(SELECT COUNT(*) FROM adverts_delivered  WHERE id_provider_service = '".$provider->id."') AS delivry_count"),
                //     DB::raw("(SELECT COUNT(*) FROM adverts_delivered WHERE id_provider_service = '".$provider->id."' AND rate != 0) AS nb_rate")
                // )->first();
                // $item->provider->delivry_count = $val->delivry_count;
                // $item->provider->rate = $val->nb_rate == 0 ? 0 : round(((float)$val->rate) / ((float)$val->nb_rate), 2);
            

                // $stateRequest = isset($request->state) ? $request->state : "";

            $queryAdverts = "SELECT adverts.id, id_user, adverts.name, adverts.description, adverts.departure_city, adverts.arrival_city, adverts.state,
                                        (CASE WHEN 
                                            (SELECT id FROM advert_responses WHERE id_advert = adverts.id AND advert_responses.id_provider_service = '".$provider->id."') 
                                            IS NOT NULL THEN 'POSTULED' ELSE 'NOT_POSTULED' END) AS status,
                                        (CASE WHEN adverts.state = '".StateAdvert::map()[StateAdvert::DELIVERED]."' THEN (SELECT rate FROM adverts_delivered WHERE adverts_delivered.id_advert = adverts.id AND adverts_delivered.id_provider_service = '".$provider->id."')
                                        ELSE 0 END) AS rate,
                                    adverts.acceptance_date, adverts.departure_date, users.firstname, users.lastname, users.profile_photo_path, users.phone, users.email,
                                    (CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify,
                                    (CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify,
                                    (CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify, 
                                    users.created_at AS user_registration_date,
                                    (CASE WHEN taken = 0 THEN 'false' ELSE 'true' END) AS taken, adverts.price, adverts.nature_package, 
                                    (SELECT COUNT(*) FROM advert_responses WHERE id_advert = adverts.id) AS provider_response_count, adverts.created_at, adverts.updated_at
                                FROM adverts, customers, users 
                                WHERE adverts.id_customer = customers.id 
                                AND customers.id_user = users.id
                                AND adverts.deleted_at IS NULL
                                AND adverts.id 
                                    IN (SELECT advert_responses.id_advert 
                                            FROM advert_responses 
                                            WHERE advert_responses.id_provider_service = '".$provider->id."' 
                                            AND advert_responses.id_advert = adverts.id)";

            if($request->has('state') && isset($request->state) && !is_numeric($request->state)) 
                return  $this->sendResponse(null, "Le state doit être un entier", "State must be an integer"); 

            if($request->has('state') && isset($request->state) && is_numeric($request->state) && ($request->state < 1 || $request->state > 10)) 
                return  $this->sendResponse(null, "Le state doit être entre 1 et 10", "The internship must be between 1 and 10"); 

            if($request->has('state') && isset($request->state))
                $queryAdverts = $queryAdverts . " AND adverts.state = '".StateAdvert::map()[$request->state]."' ";

            if($request->has('taken') && isset($request->state) && !is_bool($request->state)) 
                return  $this->sendResponse(null, "Taken doit être un entier", "Taken must be an boolean"); 

            if($request->has('taken') && isset($request->taken))
                $queryAdverts = $queryAdverts . " AND adverts.taken = '".$request->taken."' ";

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
     * Prestataire | Client: Recuperé une annonce.
     * Params(id)
     *
     */
    public function advertById($id)
    {
        try {
            $user = auth()->user();  
            //$customer  = Customer::where("id_user", $user->id)->first();  
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
 
 
    /**
     * Prestataire: Postuler sur une annonce.
     * Params(id_advert)
     *
     */
    public function applyOnAdvert(Request $request)
    { 
        try {
            $user = auth()->user();
            $provider = ProviderService::where("id_user", $user->id)->first(); 

            $validateData = Validator::make($request->all(), [
                'id_advert'=>'required' 
            ]);

            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found", 400);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
 
            if(!isset($provider)){
                if($user->id_user_type != Constants::USER_TYPE_PRESTATAIRE){
                    $msg = "Avec votre profil, vous ne pouvez pas postuler sur une annonce";
                    $debugMsg = "With your profile, you cannot postulate on an advert";
                    return  $this->sendResponse(null, $msg, $debugMsg);
                }
            }

            $advertResponse = AdvertResponse::where(["id_advert" => $request->id_advert, "id_provider_service" => $provider->id])->first();
 
            if(isset($advertResponse)){
                $msg = "Vous avez déjà postulé sur cette annonce";
                $debugMsg = "You have already applied for this advert";
                return  $this->sendResponse(null, $msg, $debugMsg);
            }

            $applyData = request()->all();   
            $applyData['id_provider_service'] = $provider->id;
            $applyData['taken'] = false;
            $applyData['price'] = isset($request->price) ? $request->price : 0; 
            $applyData['comment'] = isset($request->comment) ? $request->comment : "null";
            $applyData['acceptance_date'] = "null";
 
            $apply = AdvertResponse::create($applyData);  
 
            try{ 
                $custo = Advert::where("id", $request->id_advert)->with("customer.user")->first();

                if(isset($custo->customer->user->token_device)){
                    $message = CloudMessage::withTarget('token', $custo->customer->user->token_device)
                    ->withNotification(Notification::create("Nouvelle Proposition", "Vous avez une nouvelle proposition pour votre course de ".$custo->departure_city." vers ".$custo->arrival_city.".")) 
                    ->withData(['type' => 'type_2', 'id_advert' => $request->id_advert ]);
                    $this->messaging->send($message); 
                }

            }catch(\Throwable $ex){
                $ex->getMessage();
            }
            
            $msg = "Vous avez postulé avec succès sur l'annonce";
            return  $this->sendResponse(null, $msg, "You have successfully applied on the advert");

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }
 
    /**
     * Prestataire: l' une annonce.
     * Params(id_advert)
     *
     */
    public function deleteApply(Request $request)
    { 
        try {
            $user = auth()->user();
            $provider = ProviderService::where("id_user", $user->id)->first(); 

            $validateData = Validator::make($request->all(), [
                'id_advert'=>'required' 
            ]);

            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found", 400);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
 
            if(!isset($provider)){
                if($user->id_user_type != Constants::USER_TYPE_PRESTATAIRE){
                    $msg = "Avec votre profil, vous ne pouvez pas postuler ou ne pas postuler sur une annonce";
                    $debugMsg = "With your profile, you cannot postulate or not apply for an advertisement";
                    return  $this->sendResponse(null, $msg, $debugMsg);
                }
            }

            $advertResponse = AdvertResponse::where(["id_advert" => $request->id_advert, "id_provider_service" => $provider->id])->first();
  
            if(!isset($advertResponse)){
                $msg = "Vous n'avez pas postulé sur cette annonce";
                $debugMsg = "You have not applied for this ad";
                return  $this->sendResponse(null, $msg, $debugMsg);
            }

            
            Advert::where(["id" => $request->id_advert])->update([
                "taken" => 0,
                "state" => StateAdvert::map()[1]
            ]); 
            $advertResponse->forceDelete();
            
            $msg = "Vous avez supprimé avec succès l'annonce";
            return  $this->sendResponse(null, $msg, "Vous avez supprimé avec succès l'annonce");

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
                    return  $this->sendResponse(null, "Prestataire non trouvée", "Provider not found", 400);
 
                $msg = "Service en cours de developpement";
                return  $this->sendResponse(null, $msg, "Service under development");

            }else{
                $providerOrProvider   = Customer::where("id_user", $user->id)->first(); 
                if(!isset($providerOrProvider)) 
                    return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);
            }
            
            $validateData = Validator::make($request->all(), [
                'id'=>'required' 
            ]);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
 
            $advert = Advert::find($request->id);

            if(!isset($advert)) 
                return  $this->sendResponse(null, "Annonce non trouvée", "Advert not found", 400);
            
            $updateData = request()->all(); 
 
            if($request->has('state') && isset($request->state) && !is_numeric($request->state)) 
                return  $this->sendResponse(null, "Le state doit être un entier", "State must be an integer"); 

            if($request->has('state') && isset($request->state) && is_numeric($request->state) && ($request->state < 1 || $request->state > 10)) 
                return  $this->sendResponse(null, "Le state doit être entre 1 et 10", "The internship must be between 1 and 10"); 

  
            if($request->has("state") && isset($request->state)) {
                $updateData["state"] = $advert->state; 
                if($request->state == StateAdvert::DELETED)
                    $updateData["state"] = StateAdvert::map()[StateAdvert::DELETED]; 
            }
                 

            $res = Advert::where(["id" => $request->id, "id_customer" => $providerOrProvider->id])->update($updateData); 
            $advert = Advert::find($request->id);
            
            if($res == 0 && $providerOrProvider->id != $advert->id_customer) 
                return  $this->sendResponse(null, "Vous ne pouvez modifier que les annonces que vous avez publié", "You can only edit the adverts you posted", 400);
 
            if($advert->state == StateAdvert::map()[StateAdvert::DELETED]){
                $advert->delete();
                return  $this->sendResponse(null, "Annonce supprimée avec succès", "Advert deleted"); 
            }

            return  $this->sendResponse($advert, "Informations annonce mises à jour", "Advert informations updated"); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }

    /**
     * 
     *
     */
    public function changeStateAdvert(Request $request)
    {
        try {
            $user = auth()->user();
            $provider = ProviderService::where("id_user", $user->id)->first();

            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found", 400);
                
            
            $validateData = Validator::make($request->all(), [
                'id_advert'=>'required',
                'state'=>'required'
            ]);
    
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);

            $advert = Advert::where("id", $request->id_advert)->first();
            if(!isset($advert)) 
                return  $this->sendResponse(null, "Annonce non trouvée", "Advert not found", 400);

            //return  $this->sendResponse(StateAdvert::map()[1]);
            //return  $this->sendResponse(StateAdvert::map());
            //return  $this->sendResponse(StateAdvert::DELIVERED);
            //return  $this->sendResponse(StateAdvert::map()[StateAdvert::DELETED]; );

            if($advert->state == 1 || $advert->state == 6)
                return  $this->sendResponse(null, "Vous n'êtes pas autorisé à affecter ses states", "You are not allowed to assign its states", 400);
 
            $advertResponse = AdvertResponse::where(["id_advert" => $request->id_advert, "id_provider_service" => $provider->id, "taken" => true])->first();
            if(!isset($advertResponse)) 
                return  $this->sendResponse(null, "Vous n'êtes pas autorisé à changer l'étape de cette annonce", "You are not allowed to change the stage of this announcement", 400);
 
 
            if($request->has('rate') && isset($request->rate) && !is_numeric($request->rate)) 
                return  $this->sendResponse(null, "La note doit être un entier", "Rate must be an integer"); 

            if($request->has('rate') && isset($request->rate) && is_numeric($request->rate) && ($request->rate < 1 || $request->rate > 5)) 
                return  $this->sendResponse(null, "La note doit être entre 1 et 5", "The rate must be between 1 and 5"); 
 
            //dd($request->state);
            $advert->state = StateAdvert::map()[$request->state];

            $advert->comment = isset($request->comment) ? $request->comment : "null";
            $advert->rate = isset($request->rate) ? $request->rate : 0;
            $advert->save();

            if($request->state == StateAdvert::DELIVERED){

                $this->repo->finalizationOfDelivery($advert, $advertResponse);

                $msg = "Votre livraison est effectuée avec succès";
                $debugMsg = "Your delivery is successful"; 

                try{
                    if(isset($custo->customer->user->token_device)){
                        $custo = Advert::where("id", $advert->id)->with("customer.user")->first();
        
                        $message = CloudMessage::withTarget('token', $custo->customer->user->token_device)
                        ->withNotification(Notification::create("Livraison Terminée", "Le livreur a signalé votre course comme terminée, vous pouvez dès à présent noter sa prestation.")) 
                        ->withData(['type' => 'type_3', 'id_advert' =>  $advert->id]);
                        $this->messaging->send($message);
                    }

                }catch(\Throwable $ex){
                    $ex->getMessage();
                    
                }

            }else{
                $msg = "Annonce changée avec succès";
                $debugMsg = "Step changed successfully";

            }
            
            return  $this->sendResponse(null, $msg, $debugMsg); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        } 
    }

    
    /**
     * Prestataire: Tous les annonces d'un prestataire (les annonces qu'il a postulé ) 
     * avec comme status: ACCEPTED_STATUS | REFUSED_STATUS | WAITING_STATUS
     *
     */
    public function advertsByProvider(Request $request)
    {
        try {
            $user = auth()->user();
            $provider = ProviderService::where("id_user", $user->id)->first();
            if(!isset($provider)) 
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found", 400);
                
            $provider = ProviderService::where("id_user", $user->id)->first();   

            $queryAdverts = "SELECT Distinct(adverts.id), users.id AS id_user, adverts.name, adverts.description, adverts.departure_city, adverts.arrival_city, 
                                adverts.acceptance_date, adverts.departure_date, users.firstname, users.lastname, users.profile_photo_path, users.phone, users.email,
                                (CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify,
                                (CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify,
                                (CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify, adverts.state,
                                (CASE WHEN adverts.taken = 1 AND (SELECT taken FROM advert_responses WHERE id_provider_service = '".$provider->id."' AND  id_advert = adverts.id) = 1 THEN '".StateAdvert::map()[StateAdvert::ACCEPTED]."' 
                                      WHEN adverts.taken = 1 AND (SELECT taken FROM advert_responses WHERE id_provider_service = '".$provider->id."' AND  id_advert = adverts.id) = 0 THEN '".StateAdvert::map()[StateAdvert::REFUSED]."' 
                                       ELSE '".StateAdvert::map()[StateAdvert::WAITING]."' END) AS status ,
                                users.created_at AS user_registration_date,
                                (CASE WHEN adverts.taken = 0 THEN 'false' ELSE 'true' END) AS taken, adverts.price, adverts.nature_package, 
                                (SELECT COUNT(*) FROM advert_responses WHERE id_advert = adverts.id) AS provider_response_count, adverts.created_at, adverts.updated_at 
                            FROM adverts, customers, users
                            WHERE adverts.id_customer = customers.id 
                            AND customers.id_user = users.id  
                            AND adverts.deleted_at IS NULL
                            AND adverts.id IN (SELECT id_advert FROM advert_responses WHERE advert_responses.id_provider_service = '".$provider->id."' AND  id_advert = adverts.id )";
                            
            if($request->has('state') && isset($request->state) && !is_numeric($request->state)) 
                return  $this->sendResponse(null, "Le state doit être un entier", "State must be an integer"); 

            if($request->has('state') && isset($request->state) && is_numeric($request->state) && ($request->state < 1 || $request->state > 10)) 
                return  $this->sendResponse(null, "Le state doit être entre 1 et 10", "The internship must be between 1 and 10"); 

            if($request->has('state') && isset($request->state))
                $queryAdverts = $queryAdverts . " AND adverts.state = '".StateAdvert::map()[$request->state]."' ";

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
     * Prestataire: Tous les annonces en cours d'un client.
     *
     */
    public function advertsWithProviderStatus(Request $request)
    {
        try {
            $user = auth()->user();
            $customer = Customer::where("id_user", $user->id)->first();
            if(!isset($customer)) 
                return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);
                
            $allAdvert = Advert::where(["id_customer" => $customer->id, "taken" => true])
            ->select('id', 'departure_city', 'arrival_city', 'name', 'state', 'taken', 'description', 
                        'nature_package', 'departure_date', 'acceptance_date', 'created_at', 'updated_at')
            ->with(["advertResponse" => function($query) { 
                        $query->where('taken', true)->select('id', 'comment', 'taken', 'price', 'acceptance_date', 'id_advert', 'id_provider_service');
                    },
                    "advertResponse.provider" => function($query) use($customer) { 
                        $query->select('provider_services.id', 'id_user', 'firstname', 'firstname', 'lastname', 'profile_photo_path', 'email', 'phone', 'avis',
                                    DB::raw("(CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify"),
                                    DB::raw("(CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify"),
                                    DB::raw("(CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify"), 'email', 'users.created_at AS user_registration_date')
                                ->join('users', 'users.id', '=', 'id_user');
                    },
                    "advertResponse.notice" => function($query){
                        $query->select('rate', 'comment', 'id_advert_response');
                    }]);  

       
            if($request->has('state') && isset($request->state) && !is_numeric($request->state)) 
                return  $this->sendResponse(null, "Le state doit être un entier", "State must be an integer"); 

            if($request->has('state') && isset($request->state) && is_numeric($request->state) && ($request->state < 1 || $request->state > 10)) 
                return  $this->sendResponse(null, "Le state doit être entre 1 et 10", "The internship must be between 1 and 10"); 

            if($request->has('state') && isset($request->state))
                $allAdvert = $allAdvert->where("state", StateAdvert::map()[$request->state])->get();
            else
                $allAdvert = $allAdvert->get();

            if(isset($allAdvert) && count($allAdvert) > 0){
                $msg = "Tous les annonces";
                $debugMsg = "All advertisements";
            }else{
                $msg = "Il n'y a pas d'annonce disponible";
                $debugMsg = "There is no advert available!";
            }

            return  $this->sendResponse($allAdvert, $msg, $debugMsg); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        } 
    }

    
    /**
     * Client: Tous les prestataire qui ont postuler dans une annonces.
     *
     */
    public function providersByAdvert($id)
    {
        try {
            $user = auth()->user();

            $customer = Customer::where("id_user", $user->id)->first();  
            if(!isset($customer))
                return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);
            
            $advertResponse = AdvertResponse::where("id_advert", $id)->with("provider.user")->get();
 
            if(isset($advertResponse) && count($advertResponse) > 0){
                $msg = "liste prestataire";
                $debugMsg = "provider list!";

                foreach ($advertResponse as $item) { 
                    $val = AdvertsDelivered::where('id_provider_service', $item->provider->id)->select(
                        DB::raw("SUM(rate) as rate"),
                        DB::raw("(SELECT COUNT(*) FROM adverts_delivered  WHERE id_provider_service = '".$item->provider->id."') AS delivry_count"),
                        DB::raw("(SELECT COUNT(*) FROM adverts_delivered WHERE id_provider_service = '".$item->provider->id."' AND rate != 0) AS nb_rate")
                    )->first();
                    $item->provider->delivry_count = $val->delivry_count;
                    $item->provider->rate = $val->nb_rate == 0 ? 0 : round(((float)$val->rate) / ((float)$val->nb_rate), 2);
                } 

            }else{
                $msg = "Aucun prestataire n'a encore postulé";
                $debugMsg = "No provider has applied yet!";
            }

            return  $this->sendResponse($advertResponse, $msg, $debugMsg); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        } 
    }

 
    /**
     * Client: Choisir un prestataire.
     * Params(id_provider, id_advert)
     *
     */
    public function chooseThisProvider(Request $request)
    {
        try {
            $user = auth()->user();
            // start transaction
            DB::beginTransaction();

            $customer = Customer::where("id_user", $user->id)->first();  
            if(!isset($customer))
                return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);

            $provider = ProviderService::where("id", $request->id_provider)->with("user")->first();
            if(!isset($provider))
                return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found", 400);

            $advert = Advert::where("id", $request->id_advert)->first();
            if(!isset($advert))
                return  $this->sendResponse(null, "Annonce non trouvée", "Advert not found", 400);
    
            $advertResponse = AdvertResponse::where(["id_advert" => $request->id_advert, "id_provider_service" => $request->id_provider])->first(); 
            if(!isset($advertResponse))
                return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", "AdvertResponse not found", 400);

            if($advert->taken)
                return  $this->sendResponse(null, "Vous avez déjà choisi un prestataire, veuillez annulée la livraison pour pouvoir choisir un autre prestataire", "You have already chosen a providerService, please cancel the delivery to be able to choose another providerService", 400);
                  
            $advert->taken = true;
            $advert->state = StateAdvert::map()[StateAdvert::TAKEN];
            $advert->price = $advertResponse->price;
            $advert->acceptance_date = Carbon::now();

            $advertResponse->taken = true;
            $advertResponse->acceptance_date = Carbon::now();
            
            $advert->save();
            $advertResponse->save();
            
            $msg = "Prestataire choisi avec succès";
            $debugMsg = "Successfully chosen providerService!";
 
            try{ 
                if(isset($provider->user->token_device)){
                    $message = CloudMessage::withTarget('token', $provider->user->token_device)
                    ->withNotification(Notification::create("Offre acceptée", "Votre proposition pour la livraison ".$advert->departure_city." vers ".$advert->arrival_city." est acceptée, contactez dès à présent votre client !")) 
                    ->withData(['type' => 'type_4', 'id_advert' => $advert->id]);
                    $this->messaging->send($message); 
                }
            }catch(\Throwable $ex){
                $ex->getMessage();
                
            }
 
            // commit transaction
            DB::commit();

            return  $this->sendResponse(null, $msg, $debugMsg); 
        } catch (\Throwable $th) {
            DB::rollback();
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
