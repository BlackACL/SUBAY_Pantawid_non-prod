<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\FetsDocument;

class DashboardController extends Controller
{
    /**
     * Superadmin & Regional DPSC Dashboard
     */
    public function superadminDashboard()
    {
        $user = auth()->user();
        
        // Total employees
        $totalEmployees = User::where('access_level', 'Employee')->count();
        
        // Employees by province
        $employeesByProvince = User::where('access_level', 'Employee')
            ->whereNotNull('province')
            ->select('province', DB::raw('count(*) as count'))
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        
        // Total unserviceable units
        $totalUnserviceable = DB::table('inventory')
            ->where('STATUS', 'Unserviceable')
            ->count();
        
        // Total FETS
        $totalFets = FetsDocument::count();
        
        // Approved FETS
        $approvedFets = FetsDocument::whereIn('status', ['approved', 'completed'])
            ->whereNotNull('approved_by')
            ->count();
        
        // FETS by province (based on submitter's province)
        $fetsByProvince = FetsDocument::join('users', 'fets_documents.user_id', '=', 'users.id')
            ->whereNotNull('users.province')
            ->select('users.province', DB::raw('count(*) as count'))
            ->groupBy('users.province')
            ->orderBy('users.province')
            ->get();
        
        // FETS by status
        $fetsByStatus = FetsDocument::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Recent FETS (last 10)
        $recentFets = FetsDocument::with('submitter')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboard.superadmin', compact(
            'totalEmployees',
            'employeesByProvince',
            'totalUnserviceable',
            'totalFets',
            'approvedFets',
            'fetsByProvince',
            'fetsByStatus',
            'recentFets'
        ));
    }
    
    /**
     * Regional DPSC Dashboard
     */
    public function regionalDashboard()
    {
        $user = auth()->user();
        
        // Total employees
        $totalEmployees = User::where('access_level', 'Employee')->count();
        
        // Employees by province
        $employeesByProvince = User::where('access_level', 'Employee')
            ->whereNotNull('province')
            ->select('province', DB::raw('count(*) as count'))
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        
        // Total unserviceable units
        $totalUnserviceable = DB::table('inventory')
            ->where('STATUS', 'Unserviceable')
            ->count();
        
        // Total FETS
        $totalFets = FetsDocument::count();
        
        // Approved FETS
        $approvedFets = FetsDocument::whereIn('status', ['approved', 'completed'])
            ->whereNotNull('approved_by')
            ->count();
        
        // Verified FETS (waiting for approval)
        $verifiedFets = FetsDocument::where('status', 'verified')
            ->count();
        
        // Regional DPSC's inventory units
        $regionalInventoryCount = DB::table('inventory')
            ->where('RECEIVER', $user->fullname)
            ->where(function($q) {
                $q->whereNull('STATUS')
                  ->orWhere('STATUS', '!=', 'Unserviceable');
            })
            ->count();
        
        // All employees + Provincial DPSC inventory units (combined)
        $allEmployeeInventoryCount = DB::table('inventory')
            ->whereIn('RECEIVER', function($query) {
                $query->select('fullname')
                    ->from('users')
                    ->whereIn('access_level', ['Employee', 'Provincial DPSC']);
            })
            ->where(function($q) {
                $q->whereNull('STATUS')
                  ->orWhere('STATUS', '!=', 'Unserviceable');
            })
            ->count();
        
        // FETS by province (based on submitter's province)
        $fetsByProvince = FetsDocument::join('users', 'fets_documents.user_id', '=', 'users.id')
            ->whereNotNull('users.province')
            ->select('users.province', DB::raw('count(*) as count'))
            ->groupBy('users.province')
            ->orderBy('users.province')
            ->get();
        
        // FETS by status
        $fetsByStatus = FetsDocument::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Recent FETS (last 10)
        $recentFets = FetsDocument::with('submitter')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboard.regional', compact(
            'totalEmployees',
            'employeesByProvince',
            'totalUnserviceable',
            'totalFets',
            'approvedFets',
            'verifiedFets',
            'regionalInventoryCount',
            'allEmployeeInventoryCount',
            'fetsByProvince',
            'fetsByStatus',
            'recentFets'
        ));
    }
    
    /**
     * Provincial DPSC Dashboard
     */
    public function provincialDashboard()
    {
        $user = auth()->user();
        $province = $user->province;
        
        // Employees under this province
        $totalEmployees = User::where('access_level', 'Employee')
            ->where('province', $province)
            ->count();
        
        // FETS requests (submitted, waiting for verification)
        $fetsRequests = FetsDocument::whereHas('submitter', function($q) use ($province) {
                $q->where('province', $province);
            })
            ->where('status', 'submitted')
            ->count();
        
        // FETS being verified (verified but not yet approved)
        $fetsVerified = FetsDocument::where('status', 'verified')
            ->where('verified_by', $user->id)
            ->count();
        
        // Number of units in Provincial DPSC's inventory
        $myInventoryCount = DB::table('inventory')
            ->where('RECEIVER', $user->fullname)
            ->where(function($q) {
                $q->whereNull('STATUS')
                  ->orWhere('STATUS', '!=', 'Unserviceable');
            })
            ->count();
        
        // Number of units of all employees in the province
        $employeeInventoryCount = DB::table('inventory')
            ->whereIn('RECEIVER', function($query) use ($province) {
                $query->select('fullname')
                    ->from('users')
                    ->where('province', $province)
                    ->where('access_level', 'Employee');
            })
            ->where(function($q) {
                $q->whereNull('STATUS')
                  ->orWhere('STATUS', '!=', 'Unserviceable');
            })
            ->count();
        
        // Number of units to be returned from repair (Being Assessed for Repair status)
        $unitsForRepair = DB::table('inventory')
            ->whereIn('RECEIVER', function($query) use ($province) {
                $query->select('fullname')
                    ->from('users')
                    ->where('province', $province);
            })
            ->where('STATUS', 'Being Assessed for Repair')
            ->count();
        
        // Recent FETS for this province
        $recentFets = FetsDocument::with('submitter')
            ->whereHas('submitter', function($q) use ($province) {
                $q->where('province', $province);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // FETS by status for this province
        $fetsByStatus = FetsDocument::whereHas('submitter', function($q) use ($province) {
                $q->where('province', $province);
            })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        return view('dashboard.provincial', compact(
            'totalEmployees',
            'fetsRequests',
            'fetsVerified',
            'myInventoryCount',
            'employeeInventoryCount',
            'unitsForRepair',
            'recentFets',
            'fetsByStatus',
            'province'
        ));
    }
}
