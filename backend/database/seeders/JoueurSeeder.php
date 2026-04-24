<?php

namespace Database\Seeders;

use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\User;
use Database\Seeders\Support\SeederImageStorage;
use Illuminate\Database\Seeder;

class JoueurSeeder extends Seeder
{
    public function run(): void
    {
        $images = new SeederImageStorage();

        $joueur = User::factory()->joueur()->create([
            'name'   => 'Mohamed Tazi',
            'nom'    => 'Tazi',
            'prenom' => 'Mohamed',
            'email'  => 'joueur@easyclubsport.com',
        ]);

        $joueur->update([
            'photo' => $images->userPhoto($joueur->name, 'joueur'),
        ]);

        User::factory()
            ->joueur()
            ->count(49)
            ->create()
            ->each(function (User $user) use ($images): void {
                $user->update([
                    'photo' => $images->userPhoto($user->name, 'joueur'),
                ]);
            });

        $joueurs = User::where('role', 'joueur')->get();
        $equipes = Equipe::all();

        $joueurIndex = 0;
        foreach ($equipes as $equipe) {
            $count = min(12, $joueurs->count() - $joueurIndex);

            if ($count <= 0) {
                break;
            }

            $batch = $joueurs->slice($joueurIndex, $count);

            foreach ($batch as $joueur) {
                MembreEquipe::firstOrCreate(
                    ['equipe_id' => $equipe->id, 'utilisateur_id' => $joueur->id],
                    ['role_equipe' => 'joueur', 'date_affectation' => now()->subDays(rand(10, 180))]
                );
            }

            if ($equipe->coach_id) {
                MembreEquipe::firstOrCreate(
                    ['equipe_id' => $equipe->id, 'utilisateur_id' => $equipe->coach_id],
                    ['role_equipe' => 'coach', 'date_affectation' => now()->subDays(rand(30, 365))]
                );
            }

            $joueurIndex += $count;
        }
    }
}
