<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateServiceRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $serviceRequest;

    public $zoneIds;

    /**
     * Create a new event instance.
     *
     * $zoneIds — зоны, в которых физически находится заказчик на момент создания
     * заказа (если известны). Используется, чтобы не рассылать уведомление
     * исполнителям из зон, где категория тоже доступна, но заказчика там нет.
     */
    public function __construct(ServiceRequest $serviceRequest, array $zoneIds = [])
    {
        $this->serviceRequest = $serviceRequest;
        $this->zoneIds = $zoneIds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(){}
}
