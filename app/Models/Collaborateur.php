<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class Collaborateur
 * 
 * @property string $id
 * @property string $nom
 * @property string $prenom
 * @property string $manager
 * @property string $email
 *@property string|null $account_name
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
    use HasRoles; // Ajoutez ce trait

    //     protected $table = 'collaborateurs';

    //     // 🚨 Ajout obligatoire pour utiliser des UUIDs comme clés primaires
    //     public $incrementing = false;
    //     protected $keyType = 'string';

    //     protected $casts = [
    //         'departement_id' => 'int',
    //         'equipe_id' => 'int',
    //     ];

    //     protected $fillable = [
    //         'nom',
    //         'prenom',
    //         'manager',
    //         'email',
    //         'departement_id',
    //         'equipe_id',
    //         'activity'
    //     ];

    //     // 🚨 Génération automatique de l’UUID lors de la création
    //     protected static function boot()
    //     {
    //         parent::boot();

    //         static::creating(function ($model) {
    //             if (empty($model->{$model->getKeyName()})) {
    //                 $model->{$model->getKeyName()} = (string) Str::uuid();
    //             }
    //         });
    //     }

    //     public function reservations()
    //     {
    //         return $this->hasMany(Reservation::class, 'collaborateur_id');
    //     }

    //     public function departement()
    //     {
    //         return $this->belongsTo(Departement::class, 'departement_id');
    //     }

    //     public function equipe()
    //     {
    //         return $this->belongsTo(Equipe::class, 'equipe_id');
    //     }
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
    protected $appends = ['manager_name'];

    public function getManagerNameAttribute()
    {
        return $this->managerUser
            ? $this->managerUser->nom . ' ' . $this->managerUser->prenom
            : null;
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'collaborateur_id');
    }

    public function managerUser()
    {
        return $this->belongsTo(Collaborateur::class, 'manager', 'id');
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
