<?php

namespace App\Services;

use App\Models\Collaborateur;
use App\Models\OverrideReservation;
use App\Models\Reservation;
use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationService
{

    public function getMonthlyAvailability($year, $month, $departementId)
    {
        $currentUserId = Auth::user()->id;

        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth()->endOfDay();

        // 🔹 Si département 4 (Admin) → charger tous les départements
        $placesQuery = Place::query();

        if ($departementId != 4) {
            $placesQuery->where('departement_id', $departementId);
        } else {
            // Optionnel : seulement les départements ayant des places
            $placesQuery->whereIn('departement_id', [1, 2, 3]);
        }

        $places = $placesQuery
            ->with(['reservations' => function ($query) use ($firstDay, $lastDay) {
                $query->whereBetween('date_reservation', [$firstDay, $lastDay])->whereNull('deleted_at')
                    ->with('collaborateur');
            }])
            ->get();

        $availability = [];

        foreach ($places as $place) {
            $availability[$place->id] = [
                'name' => $place->name,
                'availability' => [],
            ];

            for ($day = 1; $day <= $lastDay->day; $day++) {
                $date = Carbon::create($year, $month, $day)->toDateString();

                if (Carbon::parse($date)->isWeekend()) {
                    $availability[$place->id]['availability'][$day] = [
                        'status' => 'weekend',
                        'reserved_by' => null,
                        'is_current_user' => false,
                    ];
                    continue;
                }

                $reservation = $place->reservations->first(function ($res) use ($date) {
                    return Carbon::parse($res->date_reservation)->toDateString() === $date;
                });

                if ($reservation) {
                    $availability[$place->id]['availability'][$day] = [
                        'status' => 'confirmed',
                        'reserved_by' => $reservation->collaborateur?->nom . ' ' . $reservation->collaborateur?->prenom,
                        'is_current_user' => $reservation->collaborateur_id === $currentUserId,
                    ];
                } else {
                    $availability[$place->id]['availability'][$day] = [
                        'status' => 'available',
                        'reserved_by' => null,
                        'is_current_user' => false,
                    ];
                }
            }
        }

        return $availability;
    }
    public function createReservations(array $data): Collection
    {
        $collaborateurId = Auth::User()->id;
        $reservations = collect();
        $dates = $data['dates'];
        foreach ($dates as $date) {
            $reservationData = [
                'collaborateur_id' => $collaborateurId,
                'date_reservation' => $date,
                'status' => 'confirmed'
            ];

            if (isset($data['place_id'])) {
                $reservationData['place_id'] = $data['place_id'];
            } elseif (isset($data['salle_id'])) {
                $reservationData['salle_id'] = $data['salle_id'];
            }
            $reservation = Reservation::create($reservationData);
            $reservations->push($reservation);
        }

        return $reservations;
    }

    public function getCalendarDays(int $year, int $month): array
    {
        $date = Carbon::create($year, $month, 1);
        $daysInMonth = $date->daysInMonth;
        $days = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::create($year, $month, $day);
            $days[$day] = [
                'date' => $currentDate->format('Y-m-d'),
                'is_weekend' => $currentDate->isWeekend(),
                'day_name' => $currentDate->dayName
            ];
        }

        return $days;
    }
    public function getOfficeLayout()
    {
        $collaborateurId = Auth::user()->id;
        $collaborateur = Collaborateur::findOrFail($collaborateurId);

        return [
            'departement_id' => $collaborateur->departement_id,
            'office_name' => $this->getOfficeName($collaborateur->departement_id),
        ];
    }

    private function getOfficeName($departementId)
    {
        return match ($departementId) {
            1 => 'P2 - Merrakech',
            2 => 'P1 - Casablanca',
            3 => 'Standard',
            default => 'Bureau Inconnu',
        };
    }

    public function getPlaces($departement_id = null)
    {
        $collaborateurId = Auth::user()->id;
        $collaborateur = Collaborateur::findOrFail($collaborateurId);

        if ($collaborateur->departement_id == 4) {
            // 🔹 Cas RH
            if ($departement_id !== null) {
                $places = Place::where('departement_id', $departement_id)
                    ->orderBy('name')
                    ->get();
            } else {
                $places = Place::all();
            }
        } else {
            // 🔹 Cas normal
            $places = Place::where('departement_id', $collaborateur->departement_id)
                ->orderBy('name')
                ->get();
        }

        return $places->map(fn($place) => [
            'id' => $place->id,
            'name' => $place->name,
            'zone' => $place->zone,
        ]);
    }

    public function getSeatBookingType()
    {
        $collaborateur = Auth::user();

        if (!$collaborateur) {
            throw new \Exception('No authenticated user');
        }

        $componentMap = [
            1 => 'SeatBookingP2',
            2 => 'SeatBookingP1',
            3 => 'SeatBooking',
            4 => 'SeatBookingRh',
        ];

        return [
            'departement_id' => $collaborateur->departement_id,
            'component_name' => $componentMap[$collaborateur->departement_id] ?? 'SeatBooking',
        ];
    }

    public function isSTL()
    {
        $collaborateurId = Auth::user()->id;
        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        return $collaborateur->roles->contains('name', 'STL');
    }

    public function getDashboardType()
    {
        $collaborateurId = Auth::user()->id;
        $collaborateur = Collaborateur::with('roles')->find($collaborateurId);

        $roleName = $collaborateur->roles->first()->name ?? 'Collaborateur';

        $dashboard = [
            'RH' => 'Dashboard-RH',
            'STL' => 'Dashboard-STL',
            'TL' => 'Dashboard-TL',
            'Collaborateur' => 'Dashboard-Collab',
        ][$roleName] ?? 'Dashboard-Collab';

        return [
            'component_name' => $dashboard,
            'role' => $roleName,
        ];
    }

    public function softDeleteUserDates($userId, $placeLabel, $dates)
    {
        // 1️⃣ Trouver la place du bon département
        $place = Place::where('name', $placeLabel)
            ->where('departement_id', $userId->departement_id)
            ->first();

        if (!$place) {
            return null;
        }

        // 2️⃣ Soft delete pour chaque date
        foreach ($dates as $date) {

            // Convertir DD-MM-YYYY → YYYY-MM-DD
            $mysqlDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');

            Reservation::where('collaborateur_id', $userId->id)
                ->where('place_id', $place->id)
                ->where('date_reservation', $mysqlDate)
                ->update([
                    'deleted_at' => now(),       // ⭐ soft delete
                    'updated_at' => now(),
                ]);
        }

        return $place;
    }

    public function createOverride($collaboratorId, $placeId, $dates, $motif, $justification, $requestedBy)
    {
        foreach ($dates as $date) {

            // 1️⃣ Convertir d-m-Y → Y-m-d
            $correctFormat = date('Y-m-d', strtotime(str_replace('/', '-', $date)));

            Log::info('🔍 CHECKING DATE', [
                'original' => $date,
                'converted' => $correctFormat
            ]);

            // 2️⃣ Chercher la réservation exacte (soft delete inclus)
            $reservation = Reservation::where('collaborateur_id', $collaboratorId)
                ->where('place_id', $placeId)
                ->where('date_reservation', $correctFormat)
                ->whereNull('deleted_at')        // IMPORTANT !!!
                ->first();

            if (!$reservation) {
                Log::warning('⚠️ NO RESERVATION FOUND FOR DATE', [
                    'date' => $correctFormat,
                    'collaboratorId' => $collaboratorId,
                    'placeId' => $placeId
                ]);
                continue;
            }

            Log::info('✅ RESERVATION FOUND', [
                'reservation_id' => $reservation->id,
                'date' => $correctFormat
            ]);

            // 3️⃣ Créer override
            OverrideReservation::create([
                'reservation_id' => $reservation->id,
                'motif'          => $motif,
                'justification'  => $justification,
                'requested_by'   => $requestedBy,
            ]);

            Log::info('✨ OVERRIDE CREATED', [
                'reservation_id' => $reservation->id,
                'date'           => $correctFormat
            ]);
        }

        Log::info('🏁 END OVERRIDE PROCESS');

        return true;
    }

    public function listOverrideRequests()
    {
        $overrides = OverrideReservation::with([
            'requester',
            'reservation',
            'reservation.collaborateur'
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return $overrides->map(function ($item) {

            // Nettoyage date
            $cleanDate = null;
            if ($item->reservation && $item->reservation->date_reservation) {
                $cleanDate = date('Y-m-d', strtotime($item->reservation->date_reservation));
            }

            return [
                'id'            => $item->id,
                'motif'         => $item->motif,
                'justification' => $item->justification,
                'date'          => $cleanDate,

                'manager'       => optional($item->requester)
                    ? trim($item->requester->prenom . ' ' . $item->requester->nom)
                    : null,

                'collaborator'  => optional(optional($item->reservation)->collaborateur)
                    ? trim($item->reservation->collaborateur->prenom . ' ' . $item->reservation->collaborateur->nom)
                    : null,
            ];
        });
    }
    public function approveOverride($overrideId)
    {
        // Charger override + reservation
        $override = OverrideReservation::with('reservation')->findOrFail($overrideId);

        // 1) Soft delete reservation
        if ($override->reservation) {
            $override->reservation->delete();  // soft delete
        }

        // 2) Soft delete override
        $override->delete(); // soft delete

        return true;
    }
}
