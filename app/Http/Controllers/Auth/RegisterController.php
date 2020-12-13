<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\AuthOtp; 
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constants;
use App\Http\Service\SendSms;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */ 

 
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    } 
 
    public function  register(Request $request){
        try {
            // start transaction
            DB::beginTransaction();

             $validateData = Validator::make($request->all(), [
                'firstname' => 'required',
                'lastname' => 'required', 
                'username' => 'required|unique:users', 
                'email' => 'email|required|unique:users',
                'password' =>'required',
                'phone' => 'required' 
            ]);

            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
            
            $path = "";
            if(isset($request->piece_identite))
                    $path = $request->file('identity_value')->store('identity');
            
            $userData = request()->all();
            $userData['password'] = bcrypt($request->password); 
            $userData['identity_value'] =  $path ; 
            $client = User::create($userData );
 
            $otpCode = Constants::OTP_CODE();

            $authOtp = AuthOtp::create(['auth'=> (string)Str::uuid(), 
                            'id_user'=> $client->id, 
                            'otp'=> bcrypt($otpCode), 
                            'otp_type' => Constants::OTP_TYPE_REGISTER]);

                            $resData['auth'] = $authOtp->auth; 
 
            // commit transaction
            DB::commit();

            //dd($userData['phone']);

            $sendMsg = new SendSms();
            $sendMsg->sendMessage($userData['phone'], "Votre code de confirmation est $otpCode.  ");

            $msg = "Un message contenant le code vous a été envoyé sur votre numéro de téléphone";
            return  $this->sendResponse($resData, $msg, $msg, 200);
        
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
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
                $auth->status = Constants::CONSUMED;

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
                $auth->status = Constants::CONSUMED;

                return  $this->sendResponse(null, "Souscription effectué avec succés.", "Souscription effectué avec succés.", 200);
            }
 
            return  $this->sendResponse(null, "Le saisi code est incorrecte.", "Le saisi code est incorrecte.", 400);
        
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
    }
 
}
