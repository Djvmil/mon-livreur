<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 12 Dec 2020 18:26:11 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Country
 * 
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $name_en_gb
 * @property string $code
 * @property string $alpha2
 * @property string $alpha3
 * @property string $name_fr_fr
 * 
 * @property \Illuminate\Database\Eloquent\Collection $users
 *
 * @package App\Models
 */
class Country extends Eloquent
{
	use SoftDeletes; 
	
	protected $fillable = [
		'name',
		'name_en_gb',
		'code',
		'alpha2',
		'alpha3',
		'name_fr_fr'
	];

	public function users()
	{
		return $this->hasMany(\App\Models\User::class, 'id_pays');
	}
}
