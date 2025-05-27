<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Reservation
 * 
 * @property int $id
 * @property int $collaborateur_id
 * @property int $place_id
 * @property int $salle_id
 * @property Carbon $date_reservation
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Reservation extends Model
{
	protected $table = 'reservations';

	protected $casts = [
		'collaborateur_id' => 'int',
		'place_id' => 'int',
		'salle_id' => 'int',
		'date_reservation' => 'datetime'
	];

	protected $fillable = [
		'collaborateur_id',
		'place_id',
		'salle_id',
		'date_reservation',
		'status'
	];

	public function collaborateur()
	{
		return $this->belongsTo(Collaborateur::class, 'collaborateur_id');
	}
	

	public function place()
	{
		return $this->belongsTo(Place::class, 'place_id');
	}
	
}
