<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\User;
use Database\Seeders\Support\SeederImageStorage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EquipeSeeder extends Seeder
{
    public function run(): void
    {
        $images = new SeederImageStorage();
        $clubs = Club::all();
        $coachs = User::where('role', 'coach')->get();
        $coachIndex = 0;

        $equipesParClub = [
            ['Lions Senior', 'Senior'],
            ['Aigles U18', 'U18'],
            ['Etoiles U16', 'U16'],
            ['Champions U14', 'U14'],
        ];

        foreach ($clubs as $club) {
            foreach ($equipesParClub as [$nom, $categorie]) {
                $coach = $coachs->get($coachIndex % $coachs->count());

                Equipe::create([
                    'club_id' => $club->id,
                    'coach_id' => $coach?->id,
                    'nom' => $nom . ' - ' . $club->ville,
                    'categorie' => $categorie,
                    'logo' => $images->equipeLogo($nom . ' ' . $club->ville),
                    'code_invitation' => strtoupper(Str::random(8)),
                    'statut' => 'active',
                    'description' => "Equipe $categorie du {$club->nom}",
                ]);

                $coachIndex++;
            }
        }
    }
}
