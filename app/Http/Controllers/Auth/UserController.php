<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use App\Models\AuthOtp; 
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants;
use App\Http\Service\SendSms;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Hash; 

class UserController extends Controller
{ 

    public function  getProfile(Request $request){
        try {
            $user = auth()->user();
            
            $user['user_type'] = auth()->user()->user_type->name;

            return  $this->sendResponse($user, "User informations", "User informations"); 
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

            //dd($validateData);

            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
            
                
            $check_type = Constants::CHECK_TYPE_REGISTER;
            if(isset($request->check_type))
                $check_type = $request->check_type;

            switch($check_type){
                case Constants::CHECK_TYPE_REGISTER: 
                    $checkResult = User::where($request->field, $request->value)->first();

                    
                    if($checkResult != null)    
                        return $this->sendResponse($checkResult, "$request->field déjà utilisé", "$request->field déjà utilisé", 400);
                      
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
    
                            $resData['auth'] = $authOtp->auth; 

                            $sendMsg = new SendSms();
                           // $sendMsg->sendMessage($request->value, "Votre code de confirmation est $otpCode.  ");
                    }

                    return $this->sendResponse($resData, $msg, $msg, 200);

                    break;
                case Constants::CHECK_TYPE_VALUE_EXIST: ;

                    $checkResult = User::where($request->field, $request->value);
                    if($checkResult != null)    
                        return $this->sendResponse($checkResult, "$request->field déjà utilisé", "$request->field déjà utilisé", 400);
                      

                    return $this->sendResponse(null, "$request->field est disponible", "$request->field est disponible", 200);
                    break;
                case Constants::CHECK_TYPE_USER: ;

                    $checkResult = User::where($request->field, $request->value)->with("user_type")->first();
                    if($checkResult != null)    
                        return $this->sendResponse($checkResult, "$request->field déjà utilisé", "$request->field déjà utilisé", 400);
                    
                    return $this->sendResponse(null, "$request->field est disponible", "$request->field  est disponible", 200);

                    break;
                default:
                    return $this->sendResponse(null, "Check type non defini", "$request->field est disponible", 200);

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
  
            $auth = AuthOtp::where('auth', $request->auth, 'status')->get()->first(); 
 

            if($auth != null && Hash::check($request->otp, $auth->otp)){
                $user = User::find($auth->id_user);
                $user->active = true;
                $user->save();
                $auth->status = Constants::STATUS_OTP_CONSUMED;

                return  $this->sendResponse(null, "Souscription effectué avec succés.", "Souscription effectué avec succés.", 200);
            }
 
            return  $this->sendResponse(null, "Le saisi code est incorrecte.", "Le saisi code est incorrecte.", 400);
        
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
    }
 
    public function  retryOtpConfirm(Request $request){
        try {
             $validateData = Validator::make($request->all(), [
                'auth' => 'required', 
                'otp' => 'required'
            ]);
            
            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
  
            $auth = AuthOtp::where('auth', $request->auth, 'status')->get()->first(); 
 

            if($auth != null && Hash::check($request->otp, $auth->otp)){
                $user = User::find($auth->id_user);
                $user->active = true;
                $user->save();
                $auth->status = Constants::STATUS_OTP_CONSUMED;

                return  $this->sendResponse(null, "Souscription effectué avec succés.", "Souscription effectué avec succés.", 200);
            }
 
            return  $this->sendResponse(null, "Le saisi code est incorrecte.", "Le saisi code est incorrecte.", 400);
        
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
    }
 
 
}
