<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClubFactory extends Factory
{
    public function definition(): array
    {
        $sports = ['Football', 'Basketball', 'Volleyball', 'Handball', 'Tennis', 'Natation', 'Athlétisme', 'Rugby'];
        $villes = ['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger', 'Agadir', 'Meknès', 'Oujda'];

        return [
            'president_id' => User::factory()->president(),
            'nom'          => 'Club ' . fake()->randomElement($sports) . ' ' . fake()->randomElement($villes),
            'logo'         => null,
            'adresse'      => fake('fr_FR')->streetAddress(),
            'telephone'    => fake('fr_FR')->phoneNumber(),
            'email'        => fake()->unique()->companyEmail(),
            'description'  => fake('fr_FR')->paragraph(2),
            'ville'        => fake()->randomElement($villes),
            'pays'         => 'Maroc',
        ];
    }
}
