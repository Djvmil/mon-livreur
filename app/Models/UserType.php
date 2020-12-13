<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 12 Dec 2020 18:26:11 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserType
 * 
 * @property int $id
 * @property string $name
 * 
 * @property \Illuminate\Database\Eloquent\Collection $users
 *
 * @package App\Models
 */
class UserType extends Eloquent
{
	use SoftDeletes; 
	
	protected $table = 'user_type';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];

	public function users()
	{
		return $this->hasMany(\App\Models\User::class, 'id_user_type');
	}
}
