<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Departement
 * 
 * @property int $id
 * @property string $label
 * @property int $STL
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Departement extends Model
{
	protected $table = 'departements';

	protected $casts = [
		'STL' => 'int'
	];

	protected $fillable = [
		'label',
		'STL'
	];
	public function collaborateur()
	{
		return $this->hasMany(Collaborateur::class, 'departement_id');
	}
}
