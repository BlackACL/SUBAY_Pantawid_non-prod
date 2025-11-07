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
        $request->validate(['name' => 'required|string|max:255|unique:repair_destinations,name']);
        $destination = RepairDestination::create(['name' => $request->name]);
        // Redirect to officials page with success message, name italicized
        $name = $destination->name;
        return redirect()->route('officials.index', ['tab' => 'repair'])->with('success', "Repair destination <span class='italic'>".e($name)."</span> added successfully!");
    }

    public function destroy($id)
    {
        $destination = RepairDestination::findOrFail($id);
        $name = $destination->name;
        $destination->delete();
        // Redirect to officials page with success message, name italicized
        return redirect()->route('officials.index', ['tab' => 'repair'])->with('success', "Repair destination <span class='italic'>".e($name)."</span> removed successfully!");
    }
}
