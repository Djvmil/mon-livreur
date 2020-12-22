<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\ProviderService;
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

            $userData = request()->all();
            if(isset($request->identity_value))
                $userData['identity_value']  = $request->file('identity_value')->store('identity');

            if(isset($request->profile_photo_path))
                $userData['profile_photo_path']  = $request->file('profile_photo_path')->store('profile');
 
            $userData['password'] = bcrypt($request->password); 
 
            $user = User::create($userData);

         /*   //if(isset($request->id_user_type) && $request->id_user_type == 1)
                
            if(isset($request->id_user_type) && $request->id_user_type == 2)
                Customer::create([
                    "id_user" => $user->id,
                    "avis" => "RAS",
                ]);
                
            else if(isset($request->id_user_type) && $request->id_user_type == 3)
                ProviderService::create([
                    "id_user" => $user->id,
                    "avis" => "RAS",
                ]); 
*/


                $type_user = $user->id_user_type;
                if($user && $type_user == Constants::USER_TYPE_CLIENT ) {
                    //$iduser=$client->get('id');
                    $datacustomer = new Customer();
                    $datacustomer->id_user = $user->id;
                    $datacustomer->avis = $request->get('avis', 'RAS');
                    $datacustomer->save();
                }
                elseif($user && $type_user == Constants::USER_TYPE_PRESTATAIRE){
                    $dataprovider = new ProviderService();
                    $dataprovider->id_user = $user->id;
                    $dataprovider->avis = $request->get('avis', 'RAS');
                    $dataprovider->save();
                }
                elseif($user && $type_user == Constants::USER_TYPE_ADMIN){
                    $dataadmin = new Admin();
                    $dataadmin->id_user = $user->id;
                    $dataadmin->avis = $request->get('avis', 'RAS');
                    $dataadmin->save();
                }
                else{
                    return  $this->sendResponse(null, 'ce type d\'utilisateur n\'existe pas', 'ce type d\'utilisateur n\'existe pas', 400);
                  
                } 
                 
            // commit transaction
            DB::commit();
            $msg = "Souscription effectué avec succés.";
            return  $this->sendResponse(null, $msg, "Subscription successfully completed");

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);
        }
   }


}
