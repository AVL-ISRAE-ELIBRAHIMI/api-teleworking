<?php

namespace App\Models\TAM;

use App\Models\Teleworking\Collaborateur;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'code_projet',
        'responsable',
    ];

    public function responsable()
    {
        return $this->belongsTo(Collaborateur::class, 'responsable');
    }


    // Relation renommée pour éviter le conflit
    public function responsableCollaborateur()
    {
        return $this->belongsTo(Collaborateur::class, 'responsable');
    }
}