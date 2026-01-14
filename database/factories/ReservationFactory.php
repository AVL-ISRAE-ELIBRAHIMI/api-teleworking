<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'collaborateur_id' => Str::uuid(),
            'date_reservation' => now()->toDateString(),
            'status' => 'confirmed',
        ];
    }
}
