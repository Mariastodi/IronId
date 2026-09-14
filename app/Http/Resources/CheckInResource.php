<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckInResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member' => new MemberResource($this->whenLoaded('member')),
            'method' => $this->method,
            'match_distance' => $this->match_distance,
            'checked_in_at' => $this->checked_in_at->toDateTimeString(),
        ];
    }
}
