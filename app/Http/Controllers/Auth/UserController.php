<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{ 

    public function  getProfile(Request $request){
        try {     
            $user= auth()->user(); 
            return  $this->sendResponse(User::find($user->id), "User informations", "User informations"); 
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->sendResponse(null, "Une erreur inconnue s'est produite.", $th->getMessage(), 422);

        }
     }

 
}
