<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController; 
use App\Models\AdvertsDelivered;
use App\Models\ProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use App\Models\AuthOtp; 
use App\Models\User;
use App\Models\Advert;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants; 
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Hash;  
use App\Http\Services\SmsService;
use App\Http\Repositories\UserRepository;

class UserController extends BaseController
{ 
    private $smsService;
    private $repo; 
    public function __construct(SmsService $smsService, UserRepository $repo)
    {
        $this->smsService = $smsService;
        $this->repo = $repo; 
    }

    public function  show(Request $request){
        try {
            $user = auth()->user();
            
            if( $user->id_user_type == Constants::USER_TYPE_CLIENT ){

                $customer = Customer::where("id_user", $user->id)->first(); 
                if(!isset($customer))
                    return  $this->sendResponse(null, "Client non trouvée", "Customer not found", 400);
    
                $allAdvert = Advert::where("id_customer", $customer->id)->get();
                
                $advertCount = $allAdvert->count();
                $user['advert_count'] = $advertCount;
                
            }else if( $user->id_user_type == Constants::USER_TYPE_PRESTATAIRE ){
                $provider = ProviderService::where("id_user", $user->id)->first();
                if(!isset($provider))
                    return  $this->sendResponse(null, "Prestataire non trouvée", "ProviderService not found", 400);

                
                    $val = AdvertsDelivered::where('id_provider_service', $provider->id)->select(
                        DB::raw("SUM(rate) as rate"),
                        DB::raw("(SELECT COUNT(*) FROM adverts_delivered  WHERE id_provider_service = '".$provider->id."') AS delivry_count"),
                        DB::raw("(SELECT COUNT(*) FROM adverts_delivered WHERE id_provider_service = '".$provider->id."' AND rate != 0) AS nb_rate")
                    )->first();


                    $user['delivry_count'] = $val->delivry_count;
                    $user['rate'] = $val->nb_rate == 0 ? 0 : round(((float)$val->rate) / ((float)$val->nb_rate), 2);

            }

            $user['user_type'] = auth()->user()->user_type->name;

            return  $this->sendResponse($user, "Informations de l'utilisateur", "User informations"); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
     }

    public function  update(Request $request){
        try {
            $user = auth()->user();
            $updateData = request()->all();
  
            if(isset($request->identity_value))
                $updateData['identity_value']  = $request->file('identity_value')->store('identity'); 
               
            if(isset($request->profile_photo_path)){ 
                $updateData['profile_photo_path']  = $request->file('profile_photo_path')->store('profile');
                $updateData['profile_photo_url']   = $updateData['profile_photo_path'];
            }

            if(isset($request->password))
                $updateData['password'] = bcrypt($request->password); 

            //return  $this->sendResponse($user);
            $res = User::where("id", $user->id)->update($updateData); 

            return  $this->sendResponse(User::find($user->id), "Informations utilisateur mises à jour", "User informations updated"); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
    }
 
    public function  check(Request $request){
 
        try {     
            $validateData = Validator::make($request->all(), [
               'field' => 'required', 
               'value' => 'required'   
            ]);
 
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
            
                
            $check_type = Constants::CHECK_TYPE_REGISTER;
            if(isset($request->check_type))
                $check_type = $request->check_type;

            switch($check_type){
                case Constants::CHECK_TYPE_REGISTER: 
                    $checkResult = User::where($request->field, $request->value)->first();

                    if($checkResult != null)    
                        return $this->sendResponse($request->value, "$request->field déjà utilisé", "$request->field Already used", 400);
                      
                    $resData = null;
                    $msg = "$request->field est disponible";

                    if($request->field == "phone"){
                        $otpCode = Constants::OTP_CODE();
                        $authOtp = AuthOtp::create([
                            'auth'=> (string)Str::uuid(),  
                            'phone'=> $request->value, 
                            'otp'=> bcrypt($otpCode), 
                            'otp_type' => Constants::OTP_TYPE_REGISTER
                            ]);

                            $msg = "Un message contenant le code vous a été envoyé sur votre numéro de téléphone";
    
                            $resData['otp'] = $otpCode; 
                            $resData['auth'] = $authOtp->auth; 

                            $sendMsg = new SmsService();
                            $sendMsg->sendMessage($request->value, "Votre code de confirmation est: $otpCode.\n Le code expire dans 10 minutes ");
                    }else if($request->field == "email"){
                        //send mail
                        // $users->notify(new NotificationMail("votre compte sera activité prochainement"));
                    
                        //send mail
                        //Mail::send(new SendMail($users,"Félicitation $users->nom, vous venez de créer votre compte particulier"));
                    }

                    return $this->sendResponse($resData, $msg, $msg);

                    break;
                case Constants::CHECK_TYPE_VALUE_EXIST: ;

                    $checkResult = User::where($request->field, $request->value);
                    if($checkResult != null)    
                        return $this->sendResponse($checkResult, "$request->field déjà utilisé", "$request->field Already used", 400);
                      

                    return $this->sendResponse(null, "$request->field est disponible", "$request->field is available");
                    break;
                case Constants::CHECK_TYPE_USER: ;

                    $checkResult = User::where($request->field, $request->value)->with("user_type")->first();
                    if($checkResult != null)    
                        return $this->sendResponse($checkResult, "$request->field déjà utilisé", "$request->field Already used", 400);
                    
                    return $this->sendResponse(null, "$request->field est disponible", "$request->field  is available");

                    break;
                default:
                    return $this->sendResponse(null, "Check type non defini", "$request->field is available");

            }

  
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
    }
  
    public function  otpConfirmation(Request $request){
        try {
             $validateData = Validator::make($request->all(), [
                'auth' => 'required', 
                'otp' => 'required'
            ]);
            
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
  
            $auth = AuthOtp::where('auth', $request->auth)->get()->first(); 
 
            if($auth != null && Hash::check($request->otp, $auth->otp)){

                //Des controles reste pour l'otp
                //-otp expire
                //-otp deja consomé
                //if($auth)
                //$user = User::where('phone', $auth->phone)->first();
                //$user->active = true;
                //$user->save(); 

                $auth->status = Constants::STATUS_OTP_CONSUMED;
                $auth->save();
                return  $this->sendResponse(null, "Confimation effectué avec succés.", "Confirmation successfully completed.");
            }
 
            return  $this->sendResponse(null, "Le code saisi est incorrecte.", "The code entered is incorrect.", 400);
        
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }
 
    // public function  retryOtpConfirm(Request $request){
    //     try {
    //          $validateData = Validator::make($request->all(), [
    //             'auth' => 'required', 
    //             'otp' => 'required'
    //         ]);
            
    //         if($validateData->fails())
    //             return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
  
    //         $auth = AuthOtp::where('auth', $request->auth, 'status')->get()->first(); 
 

    //         if($auth != null && Hash::check($request->otp, $auth->otp)){
    //             $user = User::find($auth->id_user);
    //             $user->active = true;
    //             $user->save();
    //             $auth->status = Constants::STATUS_OTP_CONSUMED;

    //             return  $this->sendResponse(null, "Souscription effectué avec succés.", "Subscription successfully completed.");
    //         }
 
    //         return  $this->sendResponse(null, "Le saisi code est incorrecte.", "The code entered is incorrect.", 400);
        
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

    //     }
    // } 


 
    public function requestForgetPassword(Request $request){
 
        try {     
            $validateData = Validator::make($request->all(), [
               'phone' => 'required',  
            ]);
 
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
            
            $checkResult = User::where("phone", $request->phone)->first();
            if(isset($checkResult)){
                $otpCode = Constants::OTP_CODE();
                $authOtp = AuthOtp::create([
                    'auth'=> (string)Str::uuid(),  
                    'phone'=> $request->phone, 
                    'otp'=> bcrypt($otpCode), 
                    'otp_type' => Constants::OTP_TYPE_FORGET_PASSWORD
                    ]);

                    $resData['otp'] = $otpCode; 
                    $resData['auth'] = $authOtp->auth; 

                    $msg = "Votre code de confirmation est: $otpCode.\n Le code expire dans 10 minutes ";

                    $sendMsg = new SmsService();
                    $sendMsg->sendMessage($request->phone, $msg);

                    return $this->sendResponse($resData, $msg, "Your confirmation code is: $otpCode. \n The code expires in 10 minutes");
            }

            return  $this->sendResponse(null, "ce numéro ($request->phone) n'existe pas", "this number ($request->phone) does not exist", 400);

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
    }
 
    public function forgetPassword(Request $request){
           try {
             $validateData = Validator::make($request->all(), [
                'auth' => 'required', 
                'otp' => 'required'
            ]);
            
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
  
            $auth = AuthOtp::where('auth', $request->auth)->first(); 
 
            if($auth != null && Hash::check($request->otp, $auth->otp)){

                //Des controles reste pour l'otp
                //-otp expire
                //-otp deja consomé
                //if($auth)
                //$user = User::where('phone', $auth->phone)->first();
                //$user->active = true;
                //$user->save(); 

                $auth->status = Constants::STATUS_OTP_CONSUMED;
                $auth->save();
                return  $this->sendResponse(null, "Confimation effectué avec succés.", "Confirmation successfully completed.");
            }
 
            return  $this->sendResponse(null, "Le code saisi est incorrecte.", "The code entered is incorrect.", 400);
        
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }
 
    public function changePassword(Request $request){
 
           try {
             $validateData = Validator::make($request->all(), [
                'auth' => 'required', 
                'password' => 'required'
            ]);
            
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
  
            $auth = AuthOtp::where('auth', $request->auth)->first(); 
            //return $this->sendResponse($auth, "terminal $request->auth");
            $checkResult = User::where("phone", $auth->phone)->update(["password"=> bcrypt($request->password)]);

            if(isset($checkResult)){
                    $msg = "Votre mot de passe à été changé avec succè";
                    $sendMsg = new SmsService();
                    $sendMsg->sendMessage($request->phone, $msg);

                    return $this->sendResponse($resData, $msg, "Your password has been successfully changed");
            }

            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 400);
        
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }


    public function image(Request $request){
        
        $path = public_path().'/storage/'.$request->path;
      //dd($path);
        return Response::download($path);        
    }

 
 
}
