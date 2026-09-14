<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Member;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return response()->json(['data' => [
            'members_count' => Member::count(),
            'enrolled_members_count' => Member::whereNotNull('face_descriptor')->count(),
            'today_check_ins_count' => CheckIn::whereDate('checked_in_at', today())->count(),
        ]]);
    }
}
