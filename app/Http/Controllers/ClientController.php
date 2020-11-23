<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client = Client::all();
        return  $client->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Client::create($request->all())) {
            return response()->json([
                'sucess' => 'Prestataire créee avec succes'

            ], 200);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        //
    }

    public function  register(Request $request){
        $validateData = $request->validate([
            'identifiant'=> 'required|max:55',
            'nom' => 'required|max:30',
            'prenom' => 'required|max:30',
            'email' => 'email|required|unique:clients',
            'password' =>'required|confirmed',
            'telephone' => 'required',
            'adresse' => 'required',
            'piece_identite'=>'mimes:jpeg,png,jpg,gif,svg,pdf|max:2048'
        ]);
        $validateData['password'] = bcrypt($request->password);
        $client = Client::create($validateData);
        $accessToken = $client->createToken('api_token')->accessToken;

        $path = $request->file('piece_identite')->store('identiteClients');
        return response(['client'=>$client, 'access_token'=>$accessToken, 'path'=>$path]);
    }

    public function login(Request $request) {
        $loginData = $request->validate([
            'email'=> 'email|required',
            'password' => 'required'
        ]);
        if (!auth()->attempt($loginData)){
            return response(['message'=>'identifiant ou mot de passe invalide']);

        }
        $accessToken = auth()->user()->createToken('api_token')->accessToken;
        return response(['client'=>auth()->user(), 'access_token'=>$accessToken]);
    }

}
