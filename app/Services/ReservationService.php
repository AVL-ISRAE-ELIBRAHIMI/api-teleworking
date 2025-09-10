<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ReservationService
{
    public function getMonthlyAvailability($year, $month, $departementId)
    {
        $currentUserId = Auth::User()->id;

        $firstDay = Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth()->endOfDay();

        $places = Place::where('departement_id', $departementId)
            ->with(['reservations' => function ($query) use ($firstDay, $lastDay) {
                $query->whereBetween('date_reservation', [$firstDay, $lastDay])
                    ->with('collaborateur'); // Charger l'utilisateur associé à chaque réservation
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
                        'is_current_user' => false
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
                        'is_current_user' => $reservation->collaborateur_id === $currentUserId
                    ];
                } else {
                    $availability[$place->id]['availability'][$day] = [
                        'status' => 'available',
                        'reserved_by' => null,
                        'is_current_user' => false
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
}
