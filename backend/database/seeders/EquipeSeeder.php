<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Equipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EquipeSeeder extends Seeder
{
    public function run(): void
    {
        $clubs  = Club::all();
        $coachs = User::where('role', 'coach')->get();
        $coachIndex = 0;

        $equipesParClub = [
            ['Lions Senior', 'Senior'],
            ['Aigles U18', 'U18'],
            ['Étoiles U16', 'U16'],
            ['Champions U14', 'U14'],
        ];

        foreach ($clubs as $club) {
            foreach ($equipesParClub as [$nom, $categorie]) {
                $coach = $coachs->get($coachIndex % $coachs->count());
                Equipe::create([
                    'club_id'         => $club->id,
                    'coach_id'        => $coach?->id,
                    'nom'             => $nom . ' - ' . $club->ville,
                    'categorie'       => $categorie,
                    'logo'            => null,
                    'code_invitation' => strtoupper(Str::random(8)),
                    'statut'          => 'active',
                    'description'     => "Équipe $categorie du {$club->nom}",
                ]);
                $coachIndex++;
            }
        }
    }
}
