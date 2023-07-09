<?php

namespace App\Http\Resources\Ticket;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user->name,
            'status' => $this->ticketStatus->name,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d h:i:s'),
            'update_at' => Carbon::parse($this->updated_at)->format('Y-m-d h:i:s'),
        ];
    }
}
