<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Collaborateur
 * 
 * @property int $id
 * @property string $nom
 * @property string $prenom
 * @property string $manager
 * @property string $email
 * @property int $departement_id
 * @property int $equipe_id
 * @property string $activity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Collaborateur extends Model
{
	protected $table = 'collaborateurs';

	protected $casts = [
		'departement_id' => 'int',
		'equipe_id' => 'int'
	];

	protected $fillable = [
		'nom',
		'prenom',
		'manager',
		'email',
		'departement_id',
		'equipe_id',
		'activity'
	];
	
	public function reservations()
    {
        return $this->hasMany(Reservation::class, 'collaborateur_id');
    }
}
