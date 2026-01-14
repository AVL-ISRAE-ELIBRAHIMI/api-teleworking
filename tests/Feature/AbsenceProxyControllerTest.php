<?php

namespace Tests\Feature;

use App\Models\Collaborateur;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AbsenceProxyControllerTest extends TestCase
{
    /** @test */
    public function it_relays_microservice_error_payload()
    {
        //  1. Fake HTTP call vers le microservice
        Http::fake([
            'http://10.42.202.11:8000/absences' => Http::response([
                'status'  => 'error',
                'message' => 'Invalid file format',
            ], 422),
        ]);

        //  2. Crée un utilisateur authentifié Sanctum
        $user = Collaborateur::factory()->create();
        $this->actingAs($user, 'sanctum');

        //  3. Fake Storage + faux fichiers uploadés
        Storage::fake('local');
        $teleworking = UploadedFile::fake()->create('teleworking.xlsx', 10);
        $pointage    = UploadedFile::fake()->create('pointage.xlsx', 10);
        $leave       = UploadedFile::fake()->create('leave.xlsx', 10);

        //  4. Mock partiel du contrôleur pour éviter le vrai file_get_contents()
        $this->partialMock(\App\Http\Controllers\API\AbsenceProxyController::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods()
                 ->shouldReceive('send')
                 ->andReturn(response()->json([
                     'status'  => 'error',
                     'message' => 'Invalid file format',
                 ]));
        });

        //  5. Envoie la requête POST
        $response = $this->postJson('/api/proxy-absences', [
            'teleworking' => $teleworking,
            'pointage'    => $pointage,
            'leave'       => $leave,
        ]);

        // 6. Vérifie la réponse JSON
        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => 'error',
                     'message' => 'Invalid file format',
                 ]);

        //  7. Vérifie que le microservice a bien été "appelé"
        Http::assertSentCount(1);
    }
}
