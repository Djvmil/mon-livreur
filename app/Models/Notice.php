<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Admin
 *
 * @property int $id
 * @property int $id_customer
 * @property string $avis
 * @property string $comment
 *
 * @property \App\Models\Customer $customer
 * @property \App\Models\ProviderService $providerService
 * *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at 
 * @package App\Models
 */
class Notice extends Eloquent
{
	use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notices';

    
	protected $casts = [
		'id_customer' => 'int',
		'id_provider' => 'int',
		'id_advert' => 'int'
	];

	protected $fillable = [
		'id_customer',
		'rate',
		'comment',
		'id_customer',
		'id_provider',
		'id_advert'
	];

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class, 'id_customer');
	}

	public function provider()
	{
		return $this->belongsTo(\App\Models\ProviderService::class, 'id_provider');
	}

	public function advert()
	{
		return $this->belongsTo(\App\Models\Advert::class, 'id_advert');
	}
 

}
