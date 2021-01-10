<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Admin
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
class Admin extends Eloquent
{
	use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admins';

    
	protected $casts = [
		'id_user' => 'int',
        'idcustomer'=>'int'
	];

	protected $fillable = [
		'id_user',
		'avis'
	];

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class, 'id_user');
	}

    public function annonce()
    {
        return $this->hasMany(\App\Models\Annonce::class, 'idcustomer');
    }

}
