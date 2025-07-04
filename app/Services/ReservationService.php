<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationService
{
    public function getMonthlyAvailability(int $year, int $month, int $departementId): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $daysInMonth = $startDate->daysInMonth;
        $places = Place::where('departement_id', $departementId)
            ->with(['reservations' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_reservation', [$startDate, $endDate]);
            }])
            ->get();

        $availability = [];

        foreach ($places as $place) {
            $placeAvailability = [];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::create($year, $month, $day);

                if ($currentDate->isWeekend()) {
                    $placeAvailability[$day] = 'weekend';
                    continue;
                }

                $reservation = $place->reservations->firstWhere('date_reservation', $currentDate->format('Y-m-d'));

                $placeAvailability[$day] = $reservation
                    ? ($reservation->collaborateur_id === auth()->id() ? 'your-booking' : 'others-booking')
                    : 'available';
            }

            $availability[$place->id] = [
                'name' => $place->name,
                'zone' => $place->zone,
                'availability' => $placeAvailability
            ];
        }

        return $availability;
    }

    public function getDailyAvailability(string $date): array
    {
        $date = Carbon::parse($date);

        $places = Place::with(['reservations' => function ($query) use ($date) {
            $query->whereDate('date_reservation', $date);
        }])->get();

        $availability = [];

        foreach ($places as $place) {
            $reservation = $place->reservations->first();

            $availability[$place->id] = [
                'name' => $place->name,
                'zone' => $place->zone,
                'status' => $reservation
                    ? ($reservation->collaborateur_id === auth()->id() ? 'your-booking' : 'others-booking')
                    : 'available'
            ];
        }

        return $availability;
    }
    public function createReservations(array $data): Collection
    {
         $collaborateurId = session('user.id');
        $reservations = collect();
        $dates = $data['dates'];
        
        dd($data, $collaborateurId);
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
