<?php

namespace Tests\Unit;

use App\Models\Collaborateur;
use Tests\TestCase;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReservationServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_creates_reservations_for_multiple_dates_with_place_id()
    {
        // 1️⃣ Simuler un utilisateur connecté
        $user = Collaborateur::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // 2️⃣ Données d’entrée 
        $data = [
            'dates' => ['2025-01-01', '2025-01-02', '2025-01-03'],
            'place_id' => 10,
        ];

        // 3️⃣ Appeler le service
        $service = new ReservationService();
        $reservations = $service->createReservations($data);

        // 4️⃣ Vérifications
        $this->assertCount(3, $reservations);
        $reservations->each(function ($reservation) use ($user, $data) {
            $this->assertEquals($user->id, $reservation->collaborateur_id);
            $this->assertEquals('confirmed', $reservation->status);
            $this->assertEquals($data['place_id'], $reservation->place_id);
        });

        // 5️⃣ Vérifie en base
        $this->assertDatabaseCount('reservations', 3);
    }
}
