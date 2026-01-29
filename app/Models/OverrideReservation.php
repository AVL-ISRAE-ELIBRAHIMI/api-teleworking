<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OverrideReservation extends Model
{
    
    protected $table = 'override_reservations';

    protected $fillable = [
        'reservation_id',
        'motif',
        'justification',
        'commentaire',
        'requested_by', // UUID
    ];

    /**
     * Relation : ce waiver appartient à une réservation
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Relation : utilisateur qui a demandé l’override (Skill TL)
     */
    public function requester()
    {
        return $this->belongsTo(Collaborateur::class, 'requested_by', 'id');
    }
}
