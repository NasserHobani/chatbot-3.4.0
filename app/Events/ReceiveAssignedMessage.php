<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\PrivateChannel;

class ReceiveAssignedMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $client;
    public $staffId;

    public function __construct($client, $staffId)
    {
        $this->client = $client;
        $this->staffId = $staffId;
    }

    public function broadcastOn()
    {
        Log::info("private channel $this->staffId");
        // return ["message-received-staff-$this->staffId"];
        return new PrivateChannel('message-received-staff-' . $this->staffId);
    }
}
