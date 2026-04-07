<?php

namespace App\Http\Resources\President\Club;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClubCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Liste des clubs recuperee avec succes.',
            'data' => [
                'clubs' => $this->collection->map(function ($club) {
                    return [
                        'id' => $club->id,
                        'president_id' => $club->president_id,
                        'nom' => $club->nom,
                        'logo' => $club->logo,
                        'logo_url' => $club->logo ? asset('storage/'.$club->logo) : null,
                        'adresse' => $club->adresse,
                        'telephone' => $club->telephone,
                        'email' => $club->email,
                        'description' => $club->description,
                        'ville' => $club->ville,
                        'pays' => $club->pays,
                        'created_at' => $club->created_at,
                        'updated_at' => $club->updated_at,
                    ];
                })->values(),
            ],
        ];
    }
}

