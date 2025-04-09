<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pointage
 * 
 * @property int $id
 * @property int $collaborateur_id
 * @property Carbon $date_pointage
 * @property bool $presence
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Pointage extends Model
{
	protected $table = 'pointages';

	protected $casts = [
		'collaborateur_id' => 'int',
		'date_pointage' => 'datetime',
		'presence' => 'bool'
	];

	protected $fillable = [
		'collaborateur_id',
		'date_pointage',
		'presence'
	];
}
