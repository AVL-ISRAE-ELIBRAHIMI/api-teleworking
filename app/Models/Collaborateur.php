<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class Collaborateur
 * @property string $id
 * @property string $nom
 * @property string $prenom
 * @property string $manager
 * @property string $email
 * @property string|null $account_name
 * @property string|null $departement_id
 * @property string|null $equipe_id
 * @property string $activity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Collaborateur extends Authenticatable
{
    protected $table = 'collaborateurs';

    // Tell Laravel that the primary key is not an auto-incrementing integer
    public $incrementing = false;

    // Tell Laravel that the primary key's data type is a string
    protected $keyType = 'string';

    // Disable remember token functionality
    protected $rememberTokenName = null;

    protected $casts = [
        // No longer need to cast these as they are strings now
        // 'departement_id' => 'int',
        // 'equipe_id' => 'int'
    ];

    protected $fillable = [
        'id',
        'nom',
        'prenom',
        'manager',
        'email',
        'account_name',
        'departement_id',
        'equipe_id',
        'activity'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'collaborateur_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    /**
     * Get the name of the "remember me" token.
     * 
     * @return string|null
     */
    public function getRememberTokenName()
    {
        return null; // Disable remember token
    }
}