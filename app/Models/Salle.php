<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Salle
 * 
 * @property int $id
 * @property string $label
 * @property int $departement_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Salle extends Model
{
	protected $table = 'salles';

	protected $casts = [
		'departement_id' => 'int'
	];

	protected $fillable = [
		'label',
		'departement_id'
	];
}
