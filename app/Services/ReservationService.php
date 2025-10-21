<?php

namespace App\Services;

use App\Models\Collaborateur;
use App\Models\Reservation;
use App\Models\Place;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
                $query->whereBetween('date_reservation', [$firstDay, $lastDay])
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
}
