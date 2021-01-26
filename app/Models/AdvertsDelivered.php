<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/** 
* @property \Carbon\Carbon $created_at
* @property \Carbon\Carbon $updated_at
* @property string $deleted_at
*/
class AdvertsDelivered extends Model
{ 
    use SoftDeletes;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'adverts_delivered';
 

	protected $casts = [ 
        'id_advert'=>'int',
        'id_provider_service'=>'int',
        'id_advert_response'=>'int',
        'id_customer'=>'int',
        'price' => 'float',
		'taken' => 'boolean',  
    ];
    
	protected $dates = [
		//'acceptance_date', 
	];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'comment',
        'taken',
        'rate',
        'price',
        'id_advert',
        'delivered_date',
        'id_provider_service', 
        'id_customer', 
        'id_advert_response', 
    ];
  
    public function advert()
    {
        return $this->belongsTo(\App\Models\Advert::class, 'id_advert');
    }
  
    public function provider()
    {
        return $this->belongsTo(\App\Models\ProviderService::class, 'id_provider_service');
    }
  
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'id_customer');
    }

    public function advertResponse()
    {
        return $this->belongsTo(\App\Models\AdvertResponse::class, 'id_advert_response');
    }
}







