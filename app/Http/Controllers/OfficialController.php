<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Official;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Manual;
use Illuminate\Support\Facades\DB;

class OfficialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    // 🔹 Index
    public function index()
    {
        $allOfficials = Official::with('user')
            ->orderBy('role')
            ->orderBy('province')
            ->orderByDesc('active')
            ->get();

        $officials = $allOfficials->groupBy(['role', 'province']);
        
        // Get repair destinations for the repair destination tab
        $destinations = \App\Models\RepairDestination::all();
        
        // Get current manual for manual management tab (only active/non-deleted)
        $currentManual = Manual::whereNull('deleted_at')->latest('uploaded_at')->first();
        
        return view('superadmin.officials.index', compact('officials', 'destinations', 'currentManual'));
    }

    // 🔹 Replace Active Official
public function update(Request $request, $activeId = null)
{
    // ✅ IMPROVED VALIDATION: Makes rules more explicit for each role type.
    $request->validate([
        'role'     => 'required|string|in:Provincial DPSC,Regional DPSC,Head of Property,Recommending,Approving',
        'province' => 'nullable|string|max:100',
        // user_id is required ONLY for DPSC roles.
        'user_id'  => 'required_if:role,Provincial DPSC,Regional DPSC|nullable|exists:users,id',
        // fullname is required ONLY for the text-based roles.
        'fullname' => 'required_if:role,Head of Property,Recommending,Approving|nullable|string|max:255',
    ]);

    DB::transaction(function () use ($request, $activeId) {

        // Deactivate previous active official if exists
        if ($activeId) {
            $prev = Official::find($activeId);
            if ($prev) $prev->update(['active' => false]);
        }

        // Determine fullname and user linkage
        // ✅ FIXED: Added 'Head of Property' to the list of text-based roles.
        if (in_array($request->role, ['Recommending', 'Approving', 'Head of Property'])) {
            // Exceptions: keep text-based fullname, no user link.
            $fullname = $request->fullname;
            $userId = null;
        } else {
            // All other roles (DPSCs) MUST be linked to a user.
            $user = User::where('id', $request->user_id)
                        ->where(function($q) use ($request) {
                            $q->where('province', $request->province)
                              ->orWhereHas('roles', fn($r) => $r->where('name', $request->role));
                        })->firstOrFail();

            $fullname = $user->fullname;
            $userId = $user->id;

            // Ensure inventory exists (this part is fine as is)
            if (!$user->inventory) {
                Inventory::create(['user_id' => $user->id]);
            }
        }

        // Create new active official
        Official::create([
            'role'     => $request->role,
            'province' => $request->province,
            'fullname' => $fullname,
            'user_id'  => $userId,
            'active'   => true,
        ]);
    });

    return redirect()->route('officials.index')
                     ->with('success', 'Official replaced successfully.');
}






    // 🔹 Fetch history
public function history($role, $province = null)
{
    $query = Official::where('role', $role)->with('user');
    if ($province && $province !== '-') $query->where('province', $province);
    else $query->whereNull('province');

    $history = $query->orderByDesc('created_at')->get([
        'id','fullname','role','province','active','created_at','user_id'
    ])->map(function($official){
        if($official->user_id && $official->user) {
            $official->fullname = $official->user->fullname;
        }
        return $official;
    });

    return response()->json($history);
}


    // 🔹 Reactivate Historical Official
    public function reactivate($id)
    {
        $target = Official::findOrFail($id);

        DB::transaction(function () use ($target) {
            // Deactivate current active in same role+province
            Official::where('role', $target->role)
                ->where('province', $target->province)
                ->where('active', true)
                ->update(['active' => false]);

            // Reactivate selected historical official
            $target->update(['active' => true]);
        });

        return redirect()->route('officials.index')
            ->with('success', "Official {$target->fullname} reactivated successfully.");
    }
}
