<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Place
 * 
 * @property int $id
 * @property string $label
 * @property int $departement_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Place extends Model
{
	protected $table = 'places';

	protected $casts = [
		'departement_id' => 'int'
	];

	protected $fillable = [
		'name',
		'zone',
		'is_active',
		'departement_id'
	];
	public function departement()
	{
		return $this->belongsTo(Departement::class);
	}
	public function reservations()
	{
		return $this->hasMany(Reservation::class);
	}
}
