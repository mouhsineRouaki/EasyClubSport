<?php

namespace App\Support;

use App\Models\Notification;

class NotificationPresenter
{
    public static function presenter(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'utilisateur_id' => $notification->utilisateur_id,
            'evenement_id' => $notification->evenement_id,
            'canal_id' => $notification->canal_id,
            'convocation_id' => $notification->convocation_id,
            'titre' => $notification->titre,
            'contenu' => $notification->contenu,
            'type_notification' => $notification->type_notification,
            'action' => $notification->action,
            'statut_action' => $notification->statut_action,
            'module_cible' => $notification->module_cible,
            'cible_id' => $notification->cible_id,
            'est_lue' => $notification->est_lue,
            'date_lecture' => $notification->date_lecture,
            'evenement' => self::evenementPayload($notification->evenement),
            'canal' => self::canalPayload($notification->canal),
            'convocation' => self::convocationPayload($notification->convocation),
            'created_at' => $notification->created_at,
            'updated_at' => $notification->updated_at,
        ];
    }

    protected static function evenementPayload($evenement): ?array
    {
        if (! $evenement) {
            return null;
        }

        return [
            'id' => $evenement->id,
            'titre' => $evenement->titre,
            'type' => $evenement->type,
            'date_debut' => $evenement->date_debut,
            'statut' => $evenement->statut,
            'statut_invitation_adversaire' => $evenement->statut_invitation_adversaire,
            'equipe' => self::equipePayload($evenement->equipe),
            'adversaire_equipe' => self::equipePayload($evenement->adversaireEquipe),
        ];
    }

    protected static function canalPayload($canal): ?array
    {
        if (! $canal) {
            return null;
        }

        return [
            'id' => $canal->id,
            'nom' => $canal->nom,
            'type_canal' => $canal->type_canal,
            'equipe_id' => $canal->equipe_id,
            'equipe' => self::equipePayload($canal->equipe),
        ];
    }

    protected static function convocationPayload($convocation): ?array
    {
        if (! $convocation) {
            return null;
        }

        return [
            'id' => $convocation->id,
            'statut' => $convocation->statut,
            'date_convocation' => $convocation->date_convocation,
            'date_confirmation' => $convocation->date_confirmation,
            'evenement' => self::evenementPayload($convocation->evenement),
        ];
    }

    protected static function equipePayload($equipe): ?array
    {
        if (! $equipe) {
            return null;
        }

        return [
            'id' => $equipe->id,
            'nom' => $equipe->nom,
            'logo_url' => $equipe->logo ? asset('storage/'.$equipe->logo) : null,
            'club' => $equipe->club ? [
                'id' => $equipe->club->id,
                'nom' => $equipe->club->nom,
                'logo_url' => $equipe->club->logo ? asset('storage/'.$equipe->club->logo) : null,
            ] : null,
        ];
    }
}
