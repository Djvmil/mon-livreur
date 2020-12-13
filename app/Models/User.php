<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 12 Dec 2020 18:26:11 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class User
 * 
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property \Carbon\Carbon $email_verified_at
 * @property string $username
 * @property string $password
 * @property int $id_identity_type
 * @property string $identity_value
 * @property string $identity_verify
 * @property string $url_img
 * @property string $address
 * @property int $id_pays
 * @property int $id_user_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $remember_token
 * 
 * @property \App\Models\IdentityType $identity_type
 * @property \App\Models\Country $country
 * @property \App\Models\UserType $user_type
 * @property \Illuminate\Database\Eloquent\Collection $customers
 * @property \Illuminate\Database\Eloquent\Collection $provider_services
 *
 * @package App\Models
 */
class User extends Authenticatable
{   
	use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
	
	protected $casts = [
		'id_identity_type' => 'int',
		'id_pays' => 'int',
		'id_user_type' => 'int'
	];

	protected $dates = [
		'email_verified_at'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'firstname',
		'lastname',
		'email',
		'email_verified_at',
		'username',
		'password',
		'phone',
		'id_identity_type',
		'identity_value',
		'identity_verify',
		'url_img',
		'address',
		'id_pays',
		'id_user_type',
		'remember_token'
	];

	public function identity_type()
	{
		return $this->belongsTo(\App\Models\IdentityType::class, 'id_identity_type');
	}

	public function country()
	{
		return $this->belongsTo(\App\Models\Country::class, 'id_pays');
	}

	public function user_type()
	{
		return $this->belongsTo(\App\Models\UserType::class, 'id_user_type');
	}

	public function customers()
	{
		return $this->hasMany(\App\Models\Customer::class, 'id_user');
	}

	public function provider_services()
	{
		return $this->hasMany(\App\Models\ProviderService::class, 'id_user');
	}
}
