<?php

namespace App\Http\Requests\Coach;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifierJoueurCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $joueur = $this->route('joueur');
        $joueurId = $joueur instanceof User ? $joueur->getKey() : $joueur;
        $joueurCourant = $joueur instanceof User ? $joueur : User::find($joueurId);
        $emailRules = ['sometimes', 'required', 'email', 'max:255'];

        if (($this->input('email') ?? '') !== ($joueurCourant?->email ?? '')) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($joueurId, 'id');
        }

        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'statut' => ['nullable', Rule::in(['actif', 'blesse', 'suspendu', 'inactif'])],
            'numero_joueur' => ['nullable', 'integer', 'min:1', 'max:99'],
            'poste_principal' => ['nullable', 'string', 'max:100'],
            'poste_secondaire' => ['nullable', 'string', 'max:100'],
            'pied_fort' => ['nullable', Rule::in(['droit', 'gauche', 'ambidextre'])],
            'note_globale' => ['nullable', 'integer', 'min:1', 'max:99'],
            'attaque' => ['nullable', 'integer', 'min:1', 'max:99'],
            'defense' => ['nullable', 'integer', 'min:1', 'max:99'],
            'vitesse' => ['nullable', 'integer', 'min:1', 'max:99'],
            'passe' => ['nullable', 'integer', 'min:1', 'max:99'],
            'dribble' => ['nullable', 'integer', 'min:1', 'max:99'],
            'physique' => ['nullable', 'integer', 'min:1', 'max:99'],
        ];
    }
}
