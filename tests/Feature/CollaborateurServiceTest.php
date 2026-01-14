<?php

// tests/Unit/CollaborateurServiceTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Collaborateur;
use App\Services\CollaborateurService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Attributes\Test; // plus de doc-comment

class CollaborateurServiceTest extends TestCase
{
    use DatabaseTransactions; // rollback automatique, pas de reset complet

    #[Test]
    public function it_updates_the_quota_of_a_collaborateur()
    {
        $collab = Collaborateur::factory()->create(['quota' => 5]);

        $request = new Request(['quota' => 9]);

        $service = new CollaborateurService();
        $result = $service->updateQuota($request, $collab->id);

        $this->assertSame($collab->id, $result['id']);
        $this->assertSame(9, $result['quota']);
        $this->assertSame(Response::HTTP_OK, $result['status']);

        $this->assertDatabaseHas('collaborateurs', [
            'id' => $collab->id,
            'quota' => 9,
        ]);
    }

    #[Test]
    public function it_fails_if_quota_is_invalid()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $collab = Collaborateur::factory()->create(['quota' => 5]);
        $request = new Request(['quota' => 50]); // hors [0..22]

        $service = new CollaborateurService();
        $service->updateQuota($request, $collab->id);
    }
}
