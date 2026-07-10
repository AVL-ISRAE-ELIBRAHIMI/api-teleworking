<?php

namespace App\Models\TAM;

use App\Models\Teleworking\Collaborateur;
use Illuminate\Database\Eloquent\Model;

class RequestAsset extends Model
{
    protected $fillable = [
        'new_location',
        'materiel_id',
        'requestor',
        'validator',
        'borrow_date',
        'return_date',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
    ];

    public function materiel()
    {
        return $this->belongsTo(Materiel::class, 'materiel_id');
    }

    public function requestor()
    {
        return $this->belongsTo(Collaborateur::class, 'requestor');
    }

    public function validator()
    {
        return $this->belongsTo(Collaborateur::class, 'validator');
    }
}
