<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganizationParsed implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $organizationId,
        public string $status
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organizations'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'organization.parsed';
    }
}