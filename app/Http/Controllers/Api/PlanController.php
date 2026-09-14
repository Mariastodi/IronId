<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        return PlanResource::collection(Plan::orderBy('price')->get());
    }

    public function store(StorePlanRequest $request)
    {
        $plan = Plan::create($request->validated());

        return new PlanResource($plan);
    }

    public function show(Plan $plan)
    {
        return new PlanResource($plan);
    }

    public function update(StorePlanRequest $request, Plan $plan)
    {
        $plan->update($request->validated());

        return new PlanResource($plan);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json(null, 204);
    }
}
