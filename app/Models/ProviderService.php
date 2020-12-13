<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 12 Dec 2020 18:26:11 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;
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
class ProviderService extends Eloquent
{
	use SoftDeletes; 
	
	public $timestamps = false;

	protected $casts = [
		'id_user' => 'int'
	];

	protected $fillable = [
		'id_user'
	];

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class, 'id_user');
	}
}
