<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;


class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request)
    {
        $activities = Activity::query();

        // Search user/email/ip
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $activities->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.ip'))) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('causer', function ($q) use ($search) {
                        $q->whereRaw('LOWER(fullname) LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
                    });
            });
        }

        

        $activities = $activities->latest()->paginate(10)->appends($request->all());
        return view('superadmin.logs_nav.logs', compact('activities'));
    }
}
