<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Annonce extends Model
{
    use HasFactory,  Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'annonces';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ville_depart',
        'ville_arrive',
        'description',
        'prix',
        'etat',
        'date_annonce',
    ];



}
