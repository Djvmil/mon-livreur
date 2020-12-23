<?php

namespace App\Http\Controllers;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PrestataireController extends Controller
{
    public $successStatus = 200;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allPrestataire()
    {
            $prestataire = Prestataire::all();
            return response()->json(['success' => $prestataire], $this->successStatus);


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Prestataire::create($request->all())) {
            return response()->json([
                'sucess' => 'Prestataire créé avec succes'

            ],200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\Response
     */
    public function show(Prestataire $prestataire)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Prestataire $prestataire)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prestataire $prestataire)
    {
        //
    }
        //authentification  with token


    public function  register(Request $request){
        $validateData = Validator::make($request->all(), [
            'identifiant'=> 'required|max:55',
            'nom' => 'required|max:30',
            'prenom' => 'required|max:30',
            'email' => 'email|required|unique:prestataires',
            'password' =>'required|confirmed',
            'telephone' => 'required',
            'adresse_depart' => 'required',
            'avis'=>'max:1000',
            'solde_compte'=>''
            //'piece_identite'=>'mimes:jpeg,png,jpg,gif,svg,pdf|max:2048'
        ]);
        if($validateData->fails()){
            return response()->json(['error'=>$validateData->errors()],401);
        }

 	$path = "";
        if(isset($request->piece_identite))
           $path = $request->file('piece_identite')->store('identitePrestataire');

        $prestataireinput=$request->all();
        $prestataireinput['password'] = bcrypt($request->password);
        $prestataire = Prestataire::create($prestataireinput);
        $success['token']= $prestataire->createToken('api_token')->accessToken;
        $success['prenom']=$prestataire->prenom;
        return response()->json(['success'=>$success, 'path'=>$path], $this->successStatus);
    }

    public function login(Request $request) {
        $loginData = Validator::make($request->all(),[
            'email'=> 'email|required',
            'password' => 'required|string'
        ]);


        if ($loginData->fails())
        {
            return response(['errors'=>$loginData->errors()->all()], 422);
        }
        $prestataire = Prestataire::where('email', $request->email)->first();
        if ($prestataire) {
            if (Hash::check($request->password, $prestataire->password)) {
                $token = $prestataire->createToken('token_prestataire')->plainTextToken;
                $response = ['client'=>$prestataire, 'token' => $token];
                return response($response, 200);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 422);
        }
    }
}
