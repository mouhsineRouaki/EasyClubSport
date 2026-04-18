<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $prenom = fake('fr_FR')->firstName();
        $nom    = fake('fr_FR')->lastName();

        return [
            'name'               => "$prenom $nom",
            'nom'                => $nom,
            'prenom'             => $prenom,
            'email'              => fake()->unique()->safeEmail(),
            'email_verified_at'  => now(),
            'password'           => static::$password ??= Hash::make('password'),
            'telephone'          => fake('fr_FR')->phoneNumber(),
            'adresse'            => fake('fr_FR')->streetAddress(),
            'photo'              => null,
            'role'               => 'joueur',
            'statut'             => 'actif',
            'remember_token'     => Str::random(10),
        ];
    }

    public function president(): static
    {
        return $this->state(fn () => ['role' => 'president']);
    }

    public function coach(): static
    {
        return $this->state(fn () => ['role' => 'coach']);
    }

    public function joueur(): static
    {
        return $this->state(fn () => ['role' => 'joueur']);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function blesse(): static
    {
        return $this->state(fn () => ['statut' => 'blesse']);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
