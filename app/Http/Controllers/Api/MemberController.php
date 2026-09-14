<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\Plan;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function __construct(
        private readonly MembershipService $membershipService,
    ) {}

    public function index(Request $request)
    {
        $members = Member::with('plan')
            ->when($request->filled('name'), fn ($query) => $query->where('name', 'like', '%'.$request->string('name').'%'))
            ->when($request->filled('matricula'), fn ($query) => $query->where('matricula', $request->string('matricula')))
            ->when($request->filled('active'), fn ($query) => $query->where('active', $request->boolean('active')))
            ->orderBy('name')
            ->paginate(max(1, min(100, $request->integer('per_page', 15))));

        return MemberResource::collection($members);
    }

    public function store(StoreMemberRequest $request)
    {
        $plan = Plan::findOrFail($request->integer('plan_id'));

        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('members', 'public')
            : null;

        $member = Member::create([
            ...$request->safe()->except('avatar'),
            'matricula' => $this->generateMatricula(),
            'avatar_path' => $avatarPath,
            'plan_started_at' => Carbon::today(),
            'plan_expires_at' => Carbon::today()->addDays($plan->duration_days),
        ]);

        return new MemberResource($member->load('plan'));
    }

    public function show(Member $member)
    {
        return new MemberResource($member->load('plan'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $member->update($request->validated());

        return new MemberResource($member->load('plan'));
    }

    public function destroy(Member $member)
    {
        if ($member->avatar_path) {
            Storage::disk('public')->delete($member->avatar_path);
        }

        $member->delete();

        return response()->json(null, 204);
    }

    public function renewPlan(Member $member)
    {
        $member = $this->membershipService->renew($member);

        return new MemberResource($member->load('plan'));
    }

    private function generateMatricula(): string
    {
        do {
            $candidate = 'ID-'.now()->format('y').'-'.Str::padLeft((string) random_int(0, 99999), 5, '0');
        } while (Member::where('matricula', $candidate)->exists());

        return $candidate;
    }
}
