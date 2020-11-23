<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrestataireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prestataire = Prestataire::all();
        return  $prestataire->toJson(JSON_PRETTY_PRINT);
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
                'sucess' => 'Prestataire créee avec succes'

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
        $validateData = $request->validate([
            'identifiant'=> 'required|max:55',
            'nom' => 'required|max:30',
            'prenom' => 'required|max:30',
            'email' => 'email|required|unique:clients',
            'password' =>'required|confirmed',
            'telephone' => 'required',
            'adresse_depart' => 'required',
            'piece_identite'=>'mimes:jpeg,png,jpg,gif,svg,pdf|max:2048'
        ]);
        $validateData['password'] = bcrypt($request->password);
        $path = $request->file('piece_identite')->store('identitePrestataire');

        $prestataire = Prestataire::create($validateData);


        $accessToken = $prestataire->createToken('accessToken')->accessToken;




        return response(['prestataire'=>$prestataire, 'access_token'=>$accessToken, 'path'=>$path]);
    }

    public function login(Request $request) {
        $loginData = $request->validate([
            'email'=> 'email|required',
            'password' => 'required'
        ]);
        if (!auth()->attempt($loginData)){
            return response(['message'=>'identifiant ou mot de passe invalide']);

        }
        $accessToken = auth()->user()->createToken('accessToken')->accessToken;
        return response(['prestataire'=>auth()->user(), 'access_token'=>$accessToken]);
    }
}
