<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Equipe
 * 
 * @property int $id
 * @property string $label
 * @property int $departement_id
 * @property int $TL
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Equipe extends Model
{
	protected $table = 'equipes';

	protected $casts = [
		'departement_id' => 'int',
		'TL' => 'int'
	];

	protected $fillable = [
		'label',
		'departement_id',
		'TL'
	];
	public function collaborateur()
	{
		return $this->hasMany(Collaborateur::class, 'equipe_id');
	}
}
