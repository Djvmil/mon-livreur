<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Passport\HasApiTokens;
use Laravel\Sanctum\HasApiTokens;

class Prestataire extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'prestataires';
    protected $primaryKey ='idprestataire';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifiant',
        'nom',
        'prenom',
        'email',
        'password',
        'piece_identite',
        'telephone',
        'solde_compte',
        'adresse_depart',
        'avis',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
