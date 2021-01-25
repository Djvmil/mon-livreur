<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 12 Dec 2020 18:26:11 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Customer
 *
 * @property int $id
 * @property int $id_user
 * @property string $avis
 *
 * @property \App\Models\User $user
 * *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at 
 * @package App\Models
 */
class Customer extends Eloquent
{
	use SoftDeletes;

	public $timestamps = false;

	protected $casts = [
		'id_user' => 'int',
        'idcustomer'=>'int',
		'rate' => 'int',
		'delivry_count' => 'int'
	];

	protected $fillable = [
		'id_user',
		'avis',
		'rate',
		'delivry_count'
	];

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class, 'id_user');
	}
 
}
