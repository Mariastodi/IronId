<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecognitionResultResource extends JsonResource
{
    public function __construct(
        private readonly mixed $member,
        private readonly float $confidence,
        private readonly mixed $checkIn,
    ) {
        parent::__construct($member);
    }

    public function toArray(Request $request): array
    {
        return [
            'recognized' => true,
            'confidence' => $this->confidence,
            'member' => new MemberResource($this->member),
            'check_in_id' => $this->checkIn->id,
            'checked_in_at' => $this->checkIn->checked_in_at->toDateTimeString(),
        ];
    }
}
