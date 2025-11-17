<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RepairDestination;

class RepairDestinationController extends Controller
{
    public function index()
    {
        $destinations = RepairDestination::all();
        return view('superadmin.officials.repair_destinations', compact('destinations'));
    }

    public function store(Request $request)
    {
        // Check if a soft-deleted record exists with this name
        $existingDeleted = RepairDestination::onlyTrashed()->where('name', $request->name)->first();
        
        if ($existingDeleted) {
            // Restore the soft-deleted record instead of creating a new one
            $existingDeleted->restore();
            $name = $existingDeleted->name;
            return redirect()->route('officials.index')->with([
                'success' => "Repair destination <span class='italic'>".e($name)."</span> restored successfully!",
                'active_tab' => 'repair'
            ]);
        }
        
        // Validate only against non-deleted records
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:repair_destinations,name,NULL,id,deleted_at,NULL'
        ]);
        
        try {
            $destination = RepairDestination::create(['name' => $validated['name']]);
            $name = $destination->name;
            return redirect()->route('officials.index')->with([
                'success' => "Repair destination <span class='italic'>".e($name)."</span> added successfully!",
                'active_tab' => 'repair'
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to create repair destination: " . $e->getMessage());
            return back()->withInput()->withErrors(['name' => 'Failed to add repair destination. Please try again.']);
        }
    }

    public function destroy($id)
    {
        try {
            $destination = RepairDestination::findOrFail($id);
            $name = $destination->name;
            $destination->delete();
            return redirect()->route('officials.index')->with([
                'success' => "Repair destination <span class='italic'>".e($name)."</span> removed successfully!",
                'active_tab' => 'repair'
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to delete repair destination: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete repair destination. Please try again.']);
        }
    }
}
