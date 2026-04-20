<?php

namespace App\Events;

use App\Models\Notification;
use App\Support\NotificationPresenter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreee implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Notification $notification)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->notification->utilisateur_id}.notifications"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.creee';
    }

    public function broadcastWith(): array
    {
        return NotificationPresenter::presenter(
            $this->notification->fresh(['evenement.equipe.club', 'evenement.adversaireEquipe.club', 'canal.equipe.club', 'convocation.evenement.equipe.club'])
        );
    }
}
