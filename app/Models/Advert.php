<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Advert extends Model
{ 
    use SoftDeletes;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'adverts';

	public $timestamps = false;

	protected $casts = [ 
        'id_customer'=>'int',
        'price' => 'float',
		'taken' => 'boolean',
    ];
    
	protected $dates = [
		'acceptance_date', 
	];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'departure_city',
        'arrival_city',
        'name',
        'state',
        'price',
        'taken',
        'description',
        'nature_package',
        'acceptance_date',
        'contact_person_name',
        'contact_person_phone',
        'id_customer',
    ];
 

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'id_customer');
    }

}







