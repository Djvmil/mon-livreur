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
class AdvertResponse extends Model
{ 
    use SoftDeletes;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'advert_responses';
 

	protected $casts = [ 
        'id_advert'=>'int',
        'id_provider_service'=>'int',
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
        'price',
        'id_advert',
        'acceptance_date',
        'id_provider_service', 
    ];
  
    public function advert()
    {
        return $this->belongsTo(\App\Models\Advert::class, 'id_advert');
    }
  
    public function provider()
    {
        return $this->belongsTo(\App\Models\ProviderService::class, 'id_provider_service');
    }

}







