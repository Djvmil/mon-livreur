<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */   
 
    public function  login(Request $request){

        try {   
            if(!isset($request->identity))
                return $this->sendResponse(null , "L'email ou le username est requis", "le champs identity est requis", 400);
            
            $v = Validator::make(request()->all(), [
                'identity' => 'required|email' 
            ]); 
 
            $identity  = request()->get('identity');
            $id = '';
            
            if($v->fails())
                $id = 'username';
            else
                $id = 'email';
                
            request()->merge([$id => $identity]); 


            $data = [
                $id  => $request->identity,
                'password' => $request->password
            ];
    
            if (auth()->attempt($data)) { 
                $resData['access_token'] = auth()->user()->createToken('MonlivreurAuth')->accessToken; 
                return  $this->sendResponse($resData, 'Connexion réussie'); 

            } else 
                return $this->sendResponse(null, 'Identifiant ou Mot de passe incorecte', "", 401); 

        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
     }


    function otpCode()
    {
        $chiffres = array('0','1','2','3','4','5','6','7','8','9');
        $positions = array_rand($chiffres, 4);
        $otpCode = null;
         
        foreach($positions as $valeur) $otpCode .= $chiffres[$valeur];
         
        return $otpCode;
    }
}
