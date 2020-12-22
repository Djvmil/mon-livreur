<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Annonce extends Model
{
    //use HasFactory,  Notifiable, HasApiTokens;

    use SoftDeletes;


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
        'city_start',
        'city_arrive',
        'name',
        'etat',
        'date_annonce',
        'nature_colis',
        'idcustomer',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'idcustomer');
    }

}
