<?php
// database/factories/CollaborateurFactory.php
namespace Database\Factories;

use App\Models\Teleworking\Collaborateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CollaborateurFactory extends Factory
{
    protected $model = Collaborateur::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),     // si UUID
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'quota' => 0,
            'departement_id' => 1,            // adapte à ton schéma
            'equipe_id' => 12,                // idem
        ];
    }
}
