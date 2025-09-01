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
        // Clear filters if requested
        if ($request->has('clear')) {
            return redirect()->route('superadmin.logs_nav.logs');
        }

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

        // Filter by role
        if ($request->filled('role')) {
            $role = $request->input('role');
            $activities->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.role'))) = ?", [strtolower($role)]);
        }

        // Filter by activity description (exact match for dropdown)
        if ($request->filled('activity')) {
            $activitySearch = $request->input('activity');
            $activities->where('description', $activitySearch);
        }

        // Date range logic
        $dateRange = $request->input('date_range');
        if ($dateRange) {
            $today = now()->startOfDay();
            $endToday = now()->endOfDay();
            switch ($dateRange) {
                case 'today':
                    $activities->whereBetween('created_at', [$today, $endToday]);
                    break;
                case 'yesterday':
                    $yesterday = now()->subDay()->startOfDay();
                    $endYesterday = now()->subDay()->endOfDay();
                    $activities->whereBetween('created_at', [$yesterday, $endYesterday]);
                    break;
                case 'last_7_days':
                    $start = now()->subDays(6)->startOfDay();
                    $activities->whereBetween('created_at', [$start, $endToday]);
                    break;
                case 'last_30_days':
                    $start = now()->subDays(29)->startOfDay();
                    $activities->whereBetween('created_at', [$start, $endToday]);
                    break;
                case 'this_month':
                    $start = now()->startOfMonth();
                    $activities->whereBetween('created_at', [$start, $endToday]);
                    break;
                case 'custom':
                    if ($request->filled('date_from') && $request->filled('date_to')) {
                        $from = $request->input('date_from') . ' 00:00:00';
                        $to = $request->input('date_to') . ' 23:59:59';
                        $activities->whereBetween('created_at', [$from, $to]);
                    } elseif ($request->filled('date_from')) {
                        $from = $request->input('date_from') . ' 00:00:00';
                        $activities->where('created_at', '>=', $from);
                    } elseif ($request->filled('date_to')) {
                        $to = $request->input('date_to') . ' 23:59:59';
                        $activities->where('created_at', '<=', $to);
                    }
                    break;
            }
        }

        $activities = $activities->latest()->paginate(10)->appends($request->all());
        return view('superadmin.logs_nav.logs', compact('activities'));
    }
}
