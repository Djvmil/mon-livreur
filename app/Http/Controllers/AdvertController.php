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
use Carbon\Carbon;
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
                                        (CASE WHEN 
                                            (SELECT id FROM advert_responses WHERE id_advert = adverts.id AND advert_responses.id_provider_service = '".$provider->id."') 
                                            IS NOT NULL THEN 'POSTULED' ELSE 'NOT_POSTULED' END) AS status,
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
                    $msg = "Avec votre profil, vous ne pouvez pas potuler sur une annonce";
                    $debugMsg = "With your profile, you cannot potulate on an advert";
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
            
            $msg = "Vous avez postulé avec succès sur l'annonce";
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
  
            if($request->has("state") && isset($request->state)) {
                $updateData["state"] = $advert->state; 
                if($request->state == Constants::DELETED_STATE)
                    $updateData["state"] = Constants::DELETED_STATE; 
            }
                 

            $res = Advert::where(["id" => $request->id, "id_customer" => $providerOrProvider->id])->update($updateData); 
            $advert = Advert::find($request->id);
            
            if($res == 0 && $providerOrProvider->id != $advert->id_customer) 
                return  $this->sendResponse(null, "Vous ne pouvez modifier que les annonces que vous avez publié", "You can only edit the adverts you posted", 400);
 
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


    /**
     * Prestataire: Tous les annonces en cours d'un client.
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

            $advert = $advert->where('state', '!=', Constants::DELIVERED_STATE)->first();
            if(!isset($advert)) 
                return  $this->sendResponse(null, "Annonce déjà livrée", "Announcement already delivered", 400);
  
            $advertResponse = AdvertResponse::where(["id_advert" => $request->id_advert, "id_provider_service" => $provider->id, "taken" => true])->first();
            if(!isset($advertResponse)) 
                return  $this->sendResponse(null, "Vous n'êtes pas autorisé à changer l'étape de cette annonce", "You are not allowed to change the stage of this announcement", 400);
 
            $advert->state = $request->state;
            $advert->save();
 
            $msg = "Étape changée avec succès";
            $debugMsg = "Step changed successfully";

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
            /*
            $queryAdverts = "SELECT Distinct(adverts.id), users.id AS id_user, adverts.name, adverts.description, adverts.departure_city, adverts.arrival_city, 
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
                                AND adverts.deleted_at IS NULL
                                AND adverts.id IN (SELECT id_advert FROM advert_responses WHERE advert_responses.id_provider_service = '".$provider->id."')
                                GROUP BY id";*/

            $queryAdverts = "SELECT Distinct(adverts.id), users.id AS id_user, adverts.name, adverts.description, adverts.departure_city, adverts.arrival_city, 
                                adverts.acceptance_date, adverts.departure_date, users.firstname, users.lastname, users.profile_photo_path,
                                (CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify,
                                (CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify,
                                (CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify, adverts.state,
                                (CASE WHEN adverts.taken = 1 AND (SELECT taken FROM advert_responses WHERE id_provider_service = '".$provider->id."' AND  id_advert = adverts.id) = 1 THEN '".Constants::ACCEPTED_STATUS."' 
                                      WHEN adverts.taken = 1 AND (SELECT taken FROM advert_responses WHERE id_provider_service = '".$provider->id."' AND  id_advert = adverts.id) = 0 THEN '".Constants::REFUSED_STATUS."' 
                                       ELSE '".Constants::WAITING_STATUS."' END) AS status ,
                                users.created_at AS user_registration_date,
                                (CASE WHEN adverts.taken = 0 THEN 'false' ELSE 'true' END) AS taken, adverts.price, adverts.nature_package, 
                                (SELECT COUNT(*) FROM advert_responses WHERE id_advert = adverts.id) AS provider_response_count, adverts.created_at, adverts.updated_at 
                            FROM adverts, customers, users
                            WHERE adverts.id_customer = customers.id 
                            AND customers.id_user = users.id  
                            AND adverts.deleted_at IS NULL
                            AND adverts.id IN (SELECT id_advert FROM advert_responses WHERE advert_responses.id_provider_service = '".$provider->id."' AND  id_advert = adverts.id)";
                            
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
                
            $allAdvert = Advert::where(["id_customer" => $customer->id, "taken" => true, ["state", '!=', Constants::DELIVERED_STATE]])
            ->select('id', 'departure_city', 'arrival_city', 'name', 'state', 'taken', 'description', 
                        'nature_package', 'departure_date', 'acceptance_date', 'created_at', 'updated_at') 
            ->with(["advertResponse" => function($query) { 
                        $query->where('taken', true)->select('id', 'comment', 'taken', 'price', 'acceptance_date', 'id_advert', 'id_provider_service');
                    },
                    "advertResponse.provider" => function($query) { 
                        $query->select('provider_services.id', 'id_user', 'avis', 'firstname', 'firstname', 'lastname', 'profile_photo_path', 'email', 'phone', 
                        DB::raw("(CASE WHEN users.is_email_verify = 0 THEN 'false' ELSE 'true' END) AS is_email_verify"),
                        DB::raw("(CASE WHEN users.is_phone_verify = 0 THEN 'false' ELSE 'true' END) AS is_phone_verify"),
                        DB::raw("(CASE WHEN users.is_identity_verify = 0 THEN 'false' ELSE 'true' END) AS is_identity_verify"), 'email', 'users.created_at AS user_registration_date')->join('users', 'users.id', '=', 'id_user');
                    }])->get();  
  
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

            $provider = ProviderService::where("id", $request->id_provider)->first();
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
            $advert->state = Constants::TAKEN_STATE;
            $advert->acceptance_date = Carbon::now();

            $advertResponse->taken = true;
            $advertResponse->acceptance_date = Carbon::now();
            
            $advert->save();
            $advertResponse->save();
            
            $msg = "Prestataire choisi avec succès";
            $debugMsg = "Successfully chosen providerService!";

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
