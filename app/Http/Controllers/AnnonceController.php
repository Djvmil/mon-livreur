<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $annonces= Annonce::all();
        return $annonces->toJson(JSON_PRETTY_PRINT);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        auth()->user();
        $request->validate([
            'city_start'=>'required',
            'city_arrive'=>'required',
            'date_annonce'=>'required',
            'name'=>'required',
            'nature_colis'=>'required'
        ]);

        $annonce= new  Annonce();
        $annonce->city_start=$request->get('city_start');
        $annonce->city_arrive=$request->get('city_arrive');
        $annonce->name=$request->get('name');
        $annonce->etat=$request->get('etat', 'en cours');
        $annonce->date_annonce=$request->get('date_annonce');
        $annonce->nature_colis=$request->get('nature_colis');
        $annonce->idcustomer= auth()->id();
        $annonce->save();
        return response()->json(['success'=>$annonce, 'message'=>'annonce créer avec success', 200]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Annonce  $annonce
     * @return \Illuminate\Http\Response
     */
    public function show(Annonce $annonce)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Annonce  $annonce
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Annonce $annonce)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Annonce  $annonce
     * @return \Illuminate\Http\Response
     */
    public function destroy(Annonce $annonce)
    {
        //
    }
}
