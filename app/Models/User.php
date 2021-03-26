<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent; 
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
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
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

	protected $fillable = [
		'firstname',
		'lastname',
		'name',
		'email',
		'is_email_verify',
		'email_verified_at',
		'username',
		'password',
		'phone',
		'is_phone_verify',
		'phone_verified_at',
		'id_identity_type',
		'identity_value',
		'is_identity_verify',
		'identity_verified_at',
		'profile_photo_path',
		'profile_photo_url',
		'url_img',
		'address',
		'id_pays',
		'id_user_type',
		'token_device',
		'remember_token'
	];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [ 
		'id_identity_type' => 'int',
		'id_pays' => 'int',
		'id_user_type' => 'int',
		'is_email_verify' => 'boolean',
		'is_phone_verify' => 'boolean',
		'is_identity_verify' => 'boolean',
		'active' => 'boolean',
    ];


	protected $dates = [
		'phone_verified_at',
		'email_verified_at',
		'identity_verified_at',
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
