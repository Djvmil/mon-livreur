<?php

namespace App\Http\Controllers;
use App\Models\Client;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;



class ClientController extends Controller
{

    public $successStatus = 200;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //$client = Auth::user();
        //return response()->json(['success' => $client], $this-> successStatus);

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
                'sucess' => 'Client créé avec succes'

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
        $validateData = Validator::make($request->all(), [
            'identifiant'=> 'required|max:55',
            'nom' => 'required|max:30',
            'prenom' => 'required|max:30',
            'email' => 'email|required|unique:clients',
            'password' =>'required|confirmed',
            'telephone' => 'required',
            'adresse' => 'required',
            'avis'=>'max:1000'
            //'piece_identite'=>'mimes:jpeg,png,jpg,gif,svg,pdf|max:2048'

        ]);

        if($validateData->fails()){
            return response()->json(['error'=>$validateData->errors()],401);
        }

	$path = "";
	if(isset($request->piece_identite))
            $path = $request->file('piece_identite')->store('identiteClients');



        $clientinput=$request->all();
        $clientinput['password'] = bcrypt($request->password);
        $client = Client::create($clientinput);
        $success['token']= $client->createToken('api_token')->accessToken;
        $success['prenom']=$client->prenom;
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
        $client = Client::where('email', $request->email)->first();
        if ($client) {
            if (Hash::check($request->password, $client->password)) {
                $token = $client->createToken('token_client')->plainTextToken;
                $response = ['client'=>$client, 'token' => $token];
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

    /**
     * All Clients.
     *
     * @return \Illuminate\Http\Response
     */
    public function allClient()
    {
        $client = Client::all();
        return response()->json(['success' => $client], $this->successStatus);


    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver('$provider')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('$provider')->user();

        // $user->token;
    }

}
