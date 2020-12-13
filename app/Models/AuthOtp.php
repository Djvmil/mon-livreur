<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 12 Dec 2020 18:26:11 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProviderService
 * 
 * @property int $id
 * @property int $id_user
 * 
 * @property \App\Models\User $user
 *
 * @package App\Models
 */
class AuthOtp extends Eloquent
{
	use SoftDeletes; 
	
	public $timestamps = false;
    protected $table = 'auth_otp';

	protected $casts = [
		'id_user' => 'int'
	];

	protected $fillable = [
		'id_user','auth','otp','otp_type', 'status'
	];

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class, 'id_user');
	}
}
