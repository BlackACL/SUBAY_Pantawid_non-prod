<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function index()
    {
        return response()->json([
            'provinces' => Place::getProvinces(),
            'municipalities' => Place::getMunicipalities(),
            'offices' => Place::getOffices()
        ]);
    }
    
    public function storeProvince(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        
        Place::create([
            'type' => Place::TYPE_PROVINCE,
            'name' => $request->name
        ]);
        
        return redirect()->route('officials.index', ['tab' => 'places'])->with('success', 'Province added successfully!');
    }
    
    public function storeMunicipality(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|exists:places,id',
            'office_name' => 'nullable|string|max:255'
        ]);
        
        // Create municipality
        $municipality = Place::create([
            'type' => Place::TYPE_MUNICIPALITY,
            'name' => $request->name,
            'parent_id' => $request->parent_id
        ]);
        
        // Create office if office_name is provided
        if ($request->filled('office_name')) {
            Place::create([
                'type' => Place::TYPE_OFFICE,
                'name' => $request->office_name,
                'parent_id' => $municipality->id
            ]);
        }
        
        return redirect()->route('officials.index', ['tab' => 'places'])->with('success', 'Municipality added successfully!');
    }
    
    public function storeOffice(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|exists:places,id'
        ]);
        
        $place = Place::create([
            'type' => Place::TYPE_OFFICE,
            'name' => $request->name,
            'parent_id' => $request->parent_id
        ]);
        
        return redirect()->route('officials.index', ['tab' => 'places'])->with('success', 'Office added successfully!');
    }
    
    public function destroy($id)
    {
        $place = Place::findOrFail($id);
        $childCount = $place->children()->count();
        
        $place->delete(); // Will cascade delete children automatically
        
        $message = $place->name . ' deleted successfully!';
        if ($childCount > 0) {
            $message .= " ($childCount child location(s) also deleted)";
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    // API endpoints for AJAX calls
    public function getProvinces()
    {
        $provinces = Place::where('type', Place::TYPE_PROVINCE)
                          ->orderBy('name')
                          ->get(['id', 'name']);
        
        return response()->json($provinces);
    }
    
    public function getMunicipalities()
    {
        $municipalities = Place::where('type', Place::TYPE_MUNICIPALITY)
                               ->orderBy('name')
                               ->get(['id', 'name', 'parent_id']);
        
        return response()->json($municipalities);
    }
    
    public function getOffices()
    {
        $offices = Place::where('type', Place::TYPE_OFFICE)
                        ->orderBy('name')
                        ->get(['id', 'name', 'parent_id']);
        
        return response()->json($offices);
    }
    
    public function getHierarchy()
    {
        $provinces = Place::where('type', Place::TYPE_PROVINCE)
                          ->with(['children' => function($query) {
                              $query->where('type', Place::TYPE_MUNICIPALITY)
                                    ->with(['children' => function($q) {
                                        $q->where('type', Place::TYPE_OFFICE)
                                          ->orderBy('name');
                                    }])
                                    ->orderBy('name');
                          }])
                          ->orderBy('name')
                          ->get();
        
        // Transform to frontend-friendly structure
        $hierarchy = $provinces->map(function($province) {
            return [
                'id' => $province->id,
                'name' => $province->name,
                'municipalities' => $province->children->map(function($municipality) {
                    return [
                        'id' => $municipality->id,
                        'name' => $municipality->name,
                        'offices' => $municipality->children->map(function($office) {
                            return [
                                'id' => $office->id,
                                'name' => $office->name
                            ];
                        })
                    ];
                })
            ];
        });
        
        return response()->json($hierarchy);
    }
}
