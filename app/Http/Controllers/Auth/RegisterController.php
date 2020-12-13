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
                'email' => 'email|required|unique:users',
                'password' =>'required',
                'phone' => 'required|unique:users' 
            ]);

            if($validateData->fails())
                return $this->sendResponse(null, $validateData->errors()->all(), $validateData->errors()->all(), 400);
            
            $path = "";
            if(isset($request->identity_value))
                    $path = $request->file('identity_value')->store('identity');
            
            $userData = request()->all();
            $userData['password'] = bcrypt($request->password); 
            $userData['identity_value'] = $path; 
            $client = User::create($userData );
  
            // commit transaction
            DB::commit(); 
            
            $msg = "Souscription effectué avec succés.";
            return  $this->sendResponse(null, $msg, $msg, 200);
        
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
   }


}
