<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Official;
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
        $allOfficials = Official::orderBy('role')
            ->orderBy('province')
            ->orderByDesc('active')
            ->get();

        $officials = $allOfficials->groupBy(['role', 'province']);
        return view('superadmin.officials.index', compact('officials'));
    }

    // 🔹 Replace Active Official
    public function update(Request $request, $activeId = null)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'role'     => 'required|string|in:Provincial DPSC,Regional DPSC,Head of Property,Recommending,Approving',
            'province' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($request, $activeId) {
            if ($activeId) {
                $prev = Official::find($activeId);
                if ($prev) $prev->update(['active' => false]);
            }

            Official::create([
                'fullname' => $request->fullname,
                'role'     => $request->role,
                'province' => $request->province,
                'active'   => true,
            ]);
        });

        return redirect()->route('officials.index')
            ->with('success', 'Official replaced successfully.');
    }

    // 🔹 Fetch history
    public function history($role, $province = null)
    {
        $query = Official::where('role', $role);
        if ($province && $province !== '-') $query->where('province', $province);
        else $query->whereNull('province');

        $history = $query->orderByDesc('created_at')->get([
            'id','fullname','role','province','active','created_at'
        ]);

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
