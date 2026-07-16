<?php

namespace App\Models\TAM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TAM\Project;


class Materiel extends Model
{
    use HasFactory;

    // Nom de la table
    protected $table = 'materiels';

    // Clé primaire
    protected $primaryKey = 'id_materiel';

    // Pas de timestamps (created_at / updated_at)
    public $timestamps = false;

    // Colonnes autorisées pour l’assignation de masse
    protected $fillable = [
        'label',
        'asset_type',
        'serial_num',
        'avl_ref',
        'purchase_date',
        'owner',
        'status',
        'purchaser',
        'project_id',
        'reference_location',
    ];

    // Casts pour les types
    protected $casts = [
        'purchase_date' => 'date',
        'serial_num'    => 'integer',
    ];
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
