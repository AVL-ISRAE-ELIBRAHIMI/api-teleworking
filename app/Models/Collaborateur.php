<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Class Collaborateur
 * 
 * @property string $id
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

    // 🚨 Ajout obligatoire pour utiliser des UUIDs comme clés primaires
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'departement_id' => 'int',
        'equipe_id' => 'int',
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

    // 🚨 Génération automatique de l’UUID lors de la création
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

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
}
