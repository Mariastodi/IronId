<?php

namespace App\Http\Controllers\Api;

use App\Enums\CheckInMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckInRequest;
use App\Http\Resources\CheckInResource;
use App\Models\CheckIn;
use App\Models\Member;
use App\Services\CheckInService;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d'], 'member_id' => ['nullable', 'integer']]);

        $checkIns = CheckIn::with('member')
            ->when($request->filled('member_id'), fn ($query) => $query->where('member_id', $request->integer('member_id')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('checked_in_at', $request->date('date')))
            ->orderByDesc('checked_in_at')
            ->paginate(max(1, min(100, $request->integer('per_page', 20))));

        return CheckInResource::collection($checkIns);
    }

    public function store(StoreCheckInRequest $request)
    {
        $member = Member::findOrFail($request->integer('member_id'));
        $checkIn = app(CheckInService::class)->record($member, CheckInMethod::Manual);

        return new CheckInResource($checkIn->load('member'));
    }
}
