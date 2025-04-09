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
 * @property string $matricule
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
		'matricule',
		'departement_id',
		'equipe_id',
		'activity'
	];
}
