<?php

namespace App\Http\Resources;

use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $membershipService = app(MembershipService::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'matricula' => $this->matricula,
            'cpf' => $this->cpf,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_path ? asset('storage/'.$this->avatar_path) : null,
            'face_enrolled' => $this->hasFaceEnrolled(),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'plan_expires_at' => $this->plan_expires_at?->toDateString(),
            'membership_status' => $membershipService->statusFor($this->resource)->value,
            'days_remaining' => $membershipService->daysRemaining($this->resource),
            'active' => $this->active,
        ];
    }
}
