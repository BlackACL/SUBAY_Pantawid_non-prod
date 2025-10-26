<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FetsDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;
use App\Models\FetsLog;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
use App\Models\Official;
use App\Models\User; // add at top if not present


class FetsController extends Controller
{

public function select(Request $request)
{
    $user = auth()->user();

    $receivers = DB::table('inventory')
        ->select('RECEIVER')
        ->distinct()
        ->where('RECEIVER', '!=', $user->fullname)
        ->pluck('RECEIVER');

    $allEquipment = DB::table('inventory')
        ->select('PROPERTY_NO', 'GENERAL_DESCRIPTION')
        ->get();

    // 🔹 Units that have been returned from repair
    $returnedFromRepairPropNos = DB::table('inventory')
        ->whereNotNull('DPO_REMARKS')
        ->where('DPO_REMARKS', 'like', 'Returned from Repair:%')
        ->pluck('PROPERTY_NO')
        ->map(fn($v) => trim($v))
        ->toArray();

    // 🔹 General lock list for all items in a pending FETS
$inProcessPropertyNos = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved'])
    ->where('transfer_movement', '!=', 'Return from Repair') // <-- ADD THIS LINE
    ->pluck('property_no')
    ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
    ->unique()
    ->toArray();

    // 🔹 ADDED: Specific lock list for items in a "For Repair" FETS
    $repairInProcessPropertyNos = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved'])
        ->where('transfer_movement', 'For Repair')
        ->pluck('property_no')
        ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
        ->unique()
        ->toArray();

    $perPage = $request->input('per_page', session('per_page', 10));
    session(['per_page' => $perPage]);

    $inventory = DB::table('inventory')
        ->where('RECEIVER', $user->fullname);

    // 🔎 Search by description, property no, or serial no
    if ($request->filled('search')) {
        $search = $request->search;
        $inventory->where(function ($query) use ($search) {
            $query->where('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                  ->orWhere('PROPERTY_NO', 'like', "%{$search}%")
                  ->orWhere('SERIAL_NO', 'like', "%{$search}%");
        });
    }

    // Handle pagination vs "Show All"
    if ($perPage === 'all') {
        $inventory = $inventory->orderBy('PROPERTY_NO')->get();
    } else {
        $inventory = $inventory->orderBy('PROPERTY_NO')
            ->paginate($perPage)
            ->appends($request->except('page'));
    }

    // Handle AJAX request
    if ($request->ajax() || $request->has('ajax')) {
        $html = '';

        foreach ($inventory as $item) {
            $disabledGeneral = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []);
            // This now uses the newly added $repairInProcessPropertyNos variable
            $disabledRepair = in_array($item->PROPERTY_NO, $repairInProcessPropertyNos ?? [])
                            && !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
            $isReturnedFromRepair = in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);

            $rowClass = ($disabledGeneral || $disabledRepair) ? 'bg-gray-100 text-gray-500 italic' : '';

            $html .= "<tr class='{$rowClass}'>";
            $html .= "<td class='p-2 text-center'>";

            // ✅ FIXED: Check for the more specific repair status FIRST
            if ($disabledRepair) {
                $html .= '<span class="text-xs inline-block bg-orange-100 text-orange-700 px-2 py-1 rounded">Being Assessed for Repair</span>';
            } elseif ($disabledGeneral) {
                $html .= '<span class="text-xs inline-block bg-yellow-100 text-yellow-700 px-2 py-1 rounded">FETS in Process</span>';
            } else {
                // This block now correctly handles both normal AND returned-from-repair items
                $html .= '<input type="checkbox" name="selected[]" value="' . $item->PROPERTY_NO . '" class="select-checkbox">';

                // We still show the status message, but it no longer blocks the checkbox
                if ($isReturnedFromRepair) {
                    $html .= '<span class="block text-xs mt-1 text-green-700 font-semibold">(Returned from Repair)</span>';
                }
            }

            $html .= "</td>";
            $html .= "<td class='p-2 text-center'>{$item->PROPERTY_NO}</td>";
            $html .= "<td class='p-2 text-center'>{$item->GENERAL_DESCRIPTION}</td>";
            $html .= "</tr>";
        }

        if (empty($html)) {
            $html = '<tr><td colspan="3" class="text-center p-2">No equipment available</td></tr>';
        }

        return response()->json(['html' => $html]);
    }

    // ✅ Fetch repair destinations
    $repairDestinations = \App\Models\RepairDestination::all();

    // ✅ Provincial DPSC
    $normalizedProvince = strtolower(trim($user->province ?? ''));
    $provincialOfficial = \App\Models\Official::where('role', 'Provincial DPSC')
        ->whereRaw('LOWER(province) = ?', [$normalizedProvince])
        ->where('active', true)
        ->first();

    // ✅ Head of Property
    $headOfProperty = \App\Models\Official::where('role', 'Head of Property')
        ->where('active', true)
        ->first();

    $provincialDisplay = $provincialOfficial
        ? "Provincial DPSC - {$provincialOfficial->fullname}"
        : "Provincial DPSC - Not Assigned";

    $headOfPropertyDisplay = $headOfProperty
        ? "Head of Property - {$headOfProperty->fullname}"
        : "Head of Property - Not Assigned";

    return view('FETS', compact(
        'receivers',
        'allEquipment',
        'inventory',
        'inProcessPropertyNos',
        'repairInProcessPropertyNos', // Pass the new variable to the view
        'returnedFromRepairPropNos',
        'repairDestinations',
        'provincialDisplay',
        'headOfPropertyDisplay'
    ));
}




public function selectEmbed(Request $request)
{
    $user = auth()->user();
    $editingFetsId = $request->input('edit'); // Get the FETS ID being edited

    // Get receivers for dropdown - exclude current user
    $receivers = DB::table('inventory')
        ->select('RECEIVER')
        ->distinct()
        ->where('RECEIVER', '!=', $user->fullname)
        ->pluck('RECEIVER');

    // All equipment for "Show All" option
    $allEquipment = DB::table('inventory')->select('PROPERTY_NO', 'GENERAL_DESCRIPTION')->get();

    // 🔹 Units that have been returned from repair
    $returnedFromRepairPropNos = DB::table('inventory')
        ->whereNotNull('DPO_REMARKS')
        ->where('DPO_REMARKS', 'like', 'Returned from Repair:%')
        ->pluck('PROPERTY_NO')
        ->map(fn($v) => trim($v))
        ->toArray();

    // 🔹 General lock list for all items in a pending FETS (EXCEPT the one being edited)
    $inProcessQuery = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved'])
        ->where('transfer_movement', '!=', 'Return from Repair');
    
    // If editing, exclude this FETS from the lock list
    if ($editingFetsId) {
        $inProcessQuery->where('id', '!=', $editingFetsId);
    }
    
    $inProcessPropertyNos = $inProcessQuery
        ->pluck('property_no')
        ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
        ->unique()
        ->toArray();

    // 🔹 Specific lock list for items in a "For Repair" FETS (EXCEPT the one being edited)
    $repairQuery = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved'])
        ->where('transfer_movement', 'For Repair');
    
    // If editing, exclude this FETS from the lock list
    if ($editingFetsId) {
        $repairQuery->where('id', '!=', $editingFetsId);
    }
    
    $repairInProcessPropertyNos = $repairQuery
        ->pluck('property_no')
        ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
        ->unique()
        ->toArray();

    // Per page setting
    $perPage = $request->input('per_page', session('per_page', 10));
    session(['per_page' => $perPage]);

    // 🔹 Parse pre-selected items for edit mode
    $selectedItems = [];
    if ($request->filled('selected_items')) {
        $selectedItems = array_map('trim', explode(',', $request->input('selected_items')));
    }

    // User inventory + search (only show items assigned to current user)
    $inventory = DB::table('inventory')
        ->where('RECEIVER', $user->fullname);

    if ($request->filled('search')) {
        $search = $request->search;
        $inventory->where(function ($query) use ($search) {
            $query->where('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                  ->orWhere('PROPERTY_NO', 'like', "%{$search}%")
                  ->orWhere('SERIAL_NO', 'like', "%{$search}%");
        });
    }

    // Handle pagination vs "Show All"
    if ($perPage === 'all') {
        $inventory = $inventory->orderBy('PROPERTY_NO')->get();
    } else {
        $inventory = $inventory->orderBy('PROPERTY_NO')
            ->paginate($perPage)
            ->appends($request->except('page'));
    }

    // Handle AJAX request
    if ($request->ajax() || $request->has('ajax')) {
        $html = '';

        foreach ($inventory as $item) {
            $disabledGeneral = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []);
            $disabledRepair = in_array($item->PROPERTY_NO, $repairInProcessPropertyNos ?? [])
                            && !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
            $isReturnedFromRepair = in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);

            $rowClass = ($disabledGeneral || $disabledRepair) ? 'bg-gray-100 text-gray-500 italic' : '';

            $html .= "<tr class='{$rowClass}'>";
            $html .= "<td class='p-2 text-center'>";

            // ✅ FIXED: Check for the more specific repair status FIRST
            if ($disabledRepair) {
                $html .= '<span class="text-xs inline-block bg-orange-100 text-orange-700 px-2 py-1 rounded">Being Assessed for Repair</span>';
            } elseif ($disabledGeneral) {
                $html .= '<span class="text-xs inline-block bg-yellow-100 text-yellow-700 px-2 py-1 rounded">FETS in Process</span>';
            } else {
                // This block now correctly handles both normal AND returned-from-repair items
                $html .= '<input type="checkbox" name="selected[]" value="' . $item->PROPERTY_NO . '" class="select-checkbox">';

                // We still show the status message, but it no longer blocks the checkbox
                if ($isReturnedFromRepair) {
                    $html .= '<span class="block text-xs mt-1 text-green-700 font-semibold">(Returned from Repair)</span>';
                }
            }

            $html .= "</td>";
            $html .= "<td class='p-2 text-center'>{$item->PROPERTY_NO}</td>";
            $html .= "<td class='p-2 text-center'>{$item->GENERAL_DESCRIPTION}</td>";
            $html .= "</tr>";
        }

        if (empty($html)) {
            $html = '<tr><td colspan="3" class="text-center p-2">No equipment available</td></tr>';
        }

        return response()->json(['html' => $html]);
    }

    // Repair destinations from DB
    $repairDestinations = \App\Models\RepairDestination::all();

    // Normalize province and find Provincial DPSC official (active)
    $normalizedProvince = strtolower(trim($user->province ?? ''));
    $provincialOfficial = \App\Models\Official::where('role', 'Provincial DPSC')
        ->whereRaw('LOWER(province) = ?', [$normalizedProvince])
        ->where('active', true)
        ->first();

    // Head of Property (active)
    $headOfProperty = \App\Models\Official::where('role', 'Head of Property')
        ->where('active', true)
        ->first();

    // Build display strings
    $provincialDisplay = $provincialOfficial
        ? "Provincial DPSC - {$provincialOfficial->fullname}"
        : "Provincial DPSC - Not Assigned";

    $headOfPropertyDisplay = $headOfProperty
        ? "Head of Property - {$headOfProperty->fullname}"
        : "Head of Property - Not Assigned";

    // Handle edit mode
    $editFets = null;
    $prefilledData = [];

    $editingFetsPropertyNos = [];
    if ($request->has('edit')) {
        $editFets = FetsDocument::where('id', $request->edit)
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->first();

        if ($editFets) {
            $prefilledData = [
                'transfer_movement' => $editFets->transfer_movement,
                'remarks' => $editFets->remarks,
                'repair_destination' => $editFets->repair_destination,
                'selected_items' => array_map('trim', explode(',', $editFets->property_no)),
            ];

            // Get property numbers that are part of this FETS being edited
            $editingFetsPropertyNos = array_map('trim', explode(',', $editFets->property_no));
        }
    }

    // Force the view to use embed layout
    config(['view.paths' => [resource_path('views')]]);

    return view('partials.FETS', [
        'receivers' => $receivers,
        'allEquipment' => $allEquipment,
        'headOfProperty' => $headOfProperty,
        'inventory' => $inventory,
        'inProcessPropertyNos' => $inProcessPropertyNos,
        'repairInProcessPropertyNos' => $repairInProcessPropertyNos,
        'returnedFromRepairPropNos' => $returnedFromRepairPropNos,
        'repairDestinations' => $repairDestinations,
        'provincialDisplay' => $provincialDisplay,
        'headOfPropertyDisplay' => $headOfPropertyDisplay,
        'editFets' => $editFets,
        'prefilledData' => $prefilledData,
        'editingFetsPropertyNos' => $editingFetsPropertyNos,
        'hideNavbar' => true,
    ]);
}


// 1️⃣ Show modal / prefilled return FETS
public function showReturnFetsModal(Request $request)
{
    $user = auth()->user();

    if ($user->access_level !== 'Provincial DPSC') {
        abort(403, 'Unauthorized.');
    }

    // ✅ Only approved "For Repair" FETS
    $fetsForRepair = FetsDocument::where('status', 'approved')
        ->where('transfer_movement', 'For Repair')
        ->whereHas('submitter', function ($q) use ($user) {
            $q->where('province', $user->province);
        })
        ->get();

    // Extract all property numbers from these FETS
    $propNos = $fetsForRepair->flatMap(function ($fets) {
        return array_map('trim', explode(',', $fets->property_no));
    })->unique()->values()->toArray();

    // Fetch units from inventory that are eligible for return
    $returnableUnits = $propNos
        ? DB::table('inventory')
            ->whereIn('PROPERTY_NO', $propNos)
            ->where(function($q) {
                $q->whereNull('DPO_REMARKS')
                  ->orWhere('DPO_REMARKS', 'not like', 'Returned from Repair%');
            })
            ->get()
        : collect();

    // 🔹 Determine the original submitters for each unit
    $unitSubmitters = [];
    foreach ($returnableUnits as $unit) {
        $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
            $propNos = array_map('trim', explode(',', $f->property_no ?? ''));
            $cleaned = array_map(fn($p) => preg_replace('/\s+/', '', $p), $propNos);
            return in_array(preg_replace('/\s+/', '', $unit->PROPERTY_NO), $cleaned, true);
        });
        $unitSubmitters[$unit->PROPERTY_NO] = $originalFets->submitter->fullname ?? null;
    }

    // Build locked property numbers for units already in return FETS (not editable)
    $lockedPropNos = FetsDocument::whereIn('status', ['submitted','verified','approved'])
        ->where('transfer_movement', 'Return from Repair')
        ->pluck('property_no')
        ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
        ->unique()
        ->toArray();

return view('partials.ReturnFETS', [
    'returnableUnits' => $returnableUnits,
    'fetsForRepair'   => $fetsForRepair,
    'lockedPropNos'   => $lockedPropNos,
    'unitSubmitters'  => $unitSubmitters,
    'hideNavbar'      => true, // added
]);
}





// 2️⃣ Submit return FETS (creates new FETS document)
public function submitReturnFets(Request $request)
{
    $request->validate([
        'selected'   => 'required|array|min:1|max:5',
        'selected.*' => 'exists:inventory,PROPERTY_NO',
        'remarks'    => 'nullable|string|max:1000',
    ]);

    $user = auth()->user();
    if ($user->access_level !== 'Provincial DPSC') {
        abort(403, 'Unauthorized.');
    }

    // Fetch selected inventory rows
    $units = DB::table('inventory')->whereIn('PROPERTY_NO', $request->selected)->get();
    if ($units->isEmpty()) {
        return back()->with('error', 'Selected equipment not found in inventory.');
    }

    // Fetch FETS that originally sent units for repair
    $fetsForRepair = FetsDocument::where('transfer_movement', 'For Repair')
        ->whereIn('status', ['submitted', 'verified', 'approved'])
        ->get();

    // Determine original submitters for selected units
    $originalSubmitters = [];
    foreach ($units as $unit) {
        $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
            $propNos = array_map('trim', explode(',', $f->property_no ?? ''));
            $cleaned = array_map(fn($p) => preg_replace('/\s+/', '', $p), $propNos);
            return in_array(preg_replace('/\s+/', '', $unit->PROPERTY_NO), $cleaned, true);
        });
        $originalSubmitters[$unit->PROPERTY_NO] = $originalFets->submitter->fullname ?? null;
    }

    // 🔹 Enforce all selected units have the same original submitter
    if (count(array_unique($originalSubmitters)) > 1) {
        return back()->with('error', 'You cannot return units from different employees in a single FETS. Select units from the same employee only.');
    }

    // Original logic mapping receivers, status, repair destinations, PDF generation...
    $originalReceivers = [];
    $previousStatus    = [];
    $previousRemarks   = [];
    $repairDestinations = [];

    foreach ($units as $unit) {
        $cleanKey = preg_replace('/\s+/', '', $unit->PROPERTY_NO);

        $originalReceivers[$cleanKey] = $unit->RECEIVER;
        $previousStatus[$cleanKey]    = $unit->STATUS;
        $previousRemarks[$cleanKey]   = $unit->DPO_REMARKS;

        $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
            $propNos = array_map('trim', explode(',', $f->property_no ?? ''));
            $cleaned = array_map(fn($p) => preg_replace('/\s+/', '', $p), $propNos);
            return in_array(preg_replace('/\s+/', '', $unit->PROPERTY_NO), $cleaned, true);
        });

        $repairDestinations[$cleanKey] = $originalFets->repair_destination ?? 'Unknown';
    }

    // Pick a receiver for PDF (first unit's original receiver)
    $toReceiver = reset($originalReceivers) ?: null;
    if (!$toReceiver) {
        return back()->with('error', 'Receiver not found for selected units.');
    }

    // Lock inventory + append "Pending Return" (existing logic)
    DB::table('inventory')
        ->whereIn('PROPERTY_NO', $request->selected)
        ->update([
            'STATUS'      => 'Pending Return',
            'DPO_REMARKS' => DB::raw("CONCAT(IFNULL(DPO_REMARKS, ''), ' | Pending Return')"),
            'updated_at'  => now(),
        ]);

    // Build fake request to reuse generate() logic (existing PDF creation)
    $fakeRequest = new Request([
        'selected' => $request->selected,
        'transfer_movement' => 'Return from Repair',
        'remarks' => $request->remarks ?? 'Returned from Repair',
        'to_receiver' => $toReceiver,
    ]);

    $pdfResult = $this->generate($fakeRequest);

    $fets = FetsDocument::latest()->first();
    if (!$fets) {
        return back()->with('error', 'Failed to create FETS PDF.');
    }

    $formData = (array) $fets->form_data;
    $formData = array_merge($formData, [
        'original_receivers'   => $originalReceivers,
        'previous_status'      => $previousStatus,
        'previous_remarks'     => $previousRemarks,
        'repair_destinations'  => $repairDestinations,
        'return_type'          => 'from_repair',
    ]);

    $fets->form_data = $formData;
    $fets->remarks = $request->remarks ?? 'Returned from Repair';
    $fets->save();

    // Logging (unchanged)
    FetsLog::create([
        'property_no' => $fets->property_no,
        'action'      => 'submitted',
        'actor'       => $user->fullname,
        'actor_role'  => $user->access_level,
        'remarks'     => "Return-from-Repair FETS #{$fets->id} submitted by {$user->fullname}",
    ]);

return back()->with([
    'success'           => "Return FETS submitted (ID {$fets->id}).",
    'fets_id'           => $fets->id,
    'fets_preview_url'  => route('fets.preview', ['id' => $fets->id]),
    'fets_download_url' => route('fets.download', ['id' => $fets->id]),
    'hideNavbar'        => true, // added as flash data
]);
}

// 🔧 Return from Repair Page (Provincial DPSC)
public function showReturnFromRepair()
{
    $user = auth()->user();

    if ($user->access_level !== 'Provincial DPSC') {
        abort(403, 'Unauthorized.');
    }

    // ✅ Only approved "For Repair" FETS from the same province
    $fetsForRepair = FetsDocument::where('status', 'approved')
        ->where('transfer_movement', 'For Repair')
        ->whereHas('submitter', function ($q) use ($user) {
            $q->where('province', $user->province);
        })
        ->get();

    // Extract all property numbers from these FETS
    $propNos = $fetsForRepair->flatMap(function ($fets) {
        return array_map('trim', explode(',', $fets->property_no));
    })->unique()->values()->toArray();

    // Fetch units from inventory that are eligible for return with pagination
    $returnableUnits = $propNos
        ? DB::table('inventory')
            ->whereIn('PROPERTY_NO', $propNos)
            ->where(function($q) {
                $q->whereNull('DPO_REMARKS')
                  ->orWhere('DPO_REMARKS', 'not like', 'Returned from Repair%');
            })
            ->paginate(10)
        : collect();

    // Determine the original submitters for each unit (handle paginated data)
    $unitSubmitters = [];
    if ($returnableUnits && method_exists($returnableUnits, 'items')) {
        foreach ($returnableUnits->items() as $unit) {
            $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
                $propNos = array_map('trim', explode(',', $f->property_no ?? ''));
                $cleaned = array_map(fn($p) => preg_replace('/\s+/', '', $p), $propNos);
                return in_array(preg_replace('/\s+/', '', $unit->PROPERTY_NO), $cleaned, true);
            });
            $unitSubmitters[$unit->PROPERTY_NO] = $originalFets->submitter->fullname ?? null;
        }
    } else {
        foreach ($returnableUnits as $unit) {
            $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
                $propNos = array_map('trim', explode(',', $f->property_no ?? ''));
                $cleaned = array_map(fn($p) => preg_replace('/\s+/', '', $p), $propNos);
                return in_array(preg_replace('/\s+/', '', $unit->PROPERTY_NO), $cleaned, true);
            });
            $unitSubmitters[$unit->PROPERTY_NO] = $originalFets->submitter->fullname ?? null;
        }
    }

    // Build locked property numbers for units already in return FETS (not editable)
    $lockedPropNos = FetsDocument::whereIn('status', ['submitted','verified','approved'])
        ->where('transfer_movement', 'Return from Repair')
        ->pluck('property_no')
        ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
        ->unique()
        ->toArray();

    return view('adminDPSC.Provincial.ReturnFromRepair', [
        'returnableUnits' => $returnableUnits,
        'fetsForRepair'   => $fetsForRepair,
        'lockedPropNos'   => $lockedPropNos,
        'unitSubmitters'  => $unitSubmitters,
    ]);
}

public function submittedEmbed()
{
    $documents = FetsDocument::where('user_id', auth()->id())
        ->with(['submitter', 'verifier', 'approver'])
        ->orderByDesc('created_at')
        ->paginate(10);

    return view('partials.SubmittedFETS', compact('documents'), [
        'hideNavbar' => true,
    ]);
}



public function generate(Request $request)
{
    // 1. Validation
    $validated = $request->validate([
        'selected'           => 'required|array|min:1|max:5',
        'transfer_movement'  => 'required|string',
        'remarks'            => 'required|string',
        'repair_destination' => 'nullable|string|exists:repair_destinations,name',
        'to_receiver'        => 'nullable|string',
    ]);

    // 2. Initial Data Gathering
    $user = auth()->user();
    $movement = $validated['transfer_movement'];
    $finalRemarks = ($movement === 'For Repair') ? 'Repair' : $validated['remarks'];
    $toPerson = $this->determineReceiver($user, $movement, $validated['remarks'], $validated);
    $allItems = DB::table('inventory')->whereIn('PROPERTY_NO', $validated['selected'])->get();

    if ($allItems->isEmpty()) {
        return back()->with('error', 'Selected equipment not found.');
    }

    // 3. Determine Template and Config
    $isLong = fn($items) => $items->contains(fn($item) =>
        strlen($item->GENERAL_DESCRIPTION ?? '') > 120 || strlen($item->PROPERTY_NO ?? '') > 20 ||
        strlen($item->SERIAL_NO ?? '') > 20 || strlen($item->PAR_NO ?? '') > 30 ||
        strlen($item->RECEIVER ?? '') > 35 || strlen($toPerson) > 35 || strlen($validated['remarks']) > 25
    );

    $useLong = $isLong($allItems);
    $itemCount = count($allItems);
    $templateName = 'FETS-FO-9' . ($useLong ? '-long' : '') . ($itemCount > 1 ? '-for' . $itemCount : '') . '.pdf';

    // ✅ --- THIS IS THE FIX --- ✅
    // First, get the entire config file as an array.
    $configSet = config('fets_coords');
    // Then, access the key from the array. This handles keys with dots correctly.
    $cfg = $configSet[$templateName] ?? null;

    if (!$cfg) {
        return back()->with('error', "No template config found for '{$templateName}'.");
    }

    $columnLayout = $configSet['item_columns'][($useLong ? 'long' : 'standard')];
    $lineHeight = $cfg['line_height'];

    // 4. PDF Generation Setup
    $pdf = new \setasign\Fpdi\Fpdi();
    $pageCount = $pdf->setSourceFile(storage_path("app/templates/{$templateName}"));

    $isPage2Allowed = in_array($templateName, ['FETS-FO-9-long.pdf','FETS-FO-9-long-for2.pdf','FETS-FO-9-long-for3.pdf','FETS-FO-9-long-for4.pdf','FETS-FO-9-long-for5.pdf']);
    $page2FieldsAlways = ['requested_by','recommending','approving','received_by'];
    $page2ExtraForMultiLong = ['from_office','from_person','to_office','to_person'];
    $fieldsOnPage2 = $isPage2Allowed ? array_merge($page2FieldsAlways, in_array($templateName, ['FETS-FO-9-long-for3.pdf','FETS-FO-9-long-for4.pdf','FETS-FO-9-long-for5.pdf']) ? $page2ExtraForMultiLong : []) : [];

    // 5. Data for Static Fields
    $staticData = [
        'fets_date'     => now()->format('F d, Y'),
        'from_office'   => 'Pantawid (RPMO)',
        'to_office'     => 'Pantawid (RPMO)',
        'from_person'   => $allItems->first()->RECEIVER ?? '',
        'to_person'     => $toPerson,
        'requested_by'  => $allItems->first()->RECEIVER ?? '',
        'received_by'   => $toPerson,
        'recommending'  => \App\Models\Official::where('role', 'Recommending')->first()->fullname ?? 'N/A',
        'approving'     => \App\Models\Official::where('role', 'Approving')->first()->fullname ?? 'N/A',
    ];

    for ($i = 1; $i <= $pageCount; $i++) {
        $tpl = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        // 6. Write Static Fields based on which page we are on
        foreach ($cfg['fields'] as $fieldName => $fieldConfig) {
            $usePage2 = in_array($fieldName, $fieldsOnPage2);
            if (isset($staticData[$fieldName]) && (($i === 1 && !$usePage2) || ($i === 2 && $usePage2))) {
                $pdf->SetXY($fieldConfig['x'], $fieldConfig['y']);
                $wrappedValue = $this->wrapDescription($staticData[$fieldName], $fieldConfig['wrap']);
                $pdf->MultiCell($fieldConfig['width'], $lineHeight, $wrappedValue, 0);
            }
        }

        // 7. Write Item Rows using the helper (ONLY on the first page)
        if ($i === 1) {
            if ($itemCount === 1) {
                $this->renderPdfItemRow($pdf, $allItems->first(), $cfg['item_row_y'], $lineHeight, $finalRemarks, $columnLayout);
            } else {
                foreach ($allItems as $idx => $item) {
                    $y = $cfg['base_y'] + ($cfg['y_offset'] * $idx);
                    $this->renderPdfItemRow($pdf, $item, $y, $lineHeight, $finalRemarks, $columnLayout);
                }
            }
        }
    }

    // 8. Save PDF and Create Database Record
    $fileName = 'fets_' . now()->format('Ymd_His') . '.pdf';
    $filePath = "public/fets/{$fileName}";
    Storage::put($filePath, $pdf->Output('S'));

    $fets = FetsDocument::create([
        'property_no'        => implode(',', $validated['selected']),
        'to_receiver'        => $toPerson,
        'remarks'            => $finalRemarks,
        'transfer_movement'  => $movement,
        'repair_destination' => ($movement === 'For Repair') ? $validated['repair_destination'] : null,
        'user_id'            => $user->id,
        'file_name'          => $fileName,
        'file_path'          => $filePath,
        'status'             => 'submitted',
    ]);

    // 9. Redirect
    $redirectRoute = $request->has('embed') ? 'fets.select.embed' : 'fets.select';
    return redirect()->route($redirectRoute)->with([
        'success'           => 'FETS submitted and PDF generated.',
        'fets_id'           => $fets->id,
        'fets_preview_url'  => route('fets.preview', ['id' => $fets->id]),
        'fets_download_url' => route('fets.download', ['id' => $fets->id]),
    ]);
}


/**
 * Renders a single inventory item row onto the PDF using a dynamic column layout.
 */
private function renderPdfItemRow(&$pdf, $item, $y, $lineHeight, $remarks, $columnLayout)
{
    foreach ($columnLayout as $field => $config) {
        $value = match($field) {
            'property_no' => $item->PROPERTY_NO,
            'serial_no'   => $item->SERIAL_NO ?? '',
            'description' => $item->GENERAL_DESCRIPTION ?? '',
            'par_no'      => $item->PAR_NO ?? '',
            'remarks'     => $remarks,
            default       => ''
        };
        $pdf->SetXY($config['x'], $y);
        $wrappedValue = $this->wrapDescription($value, $config['wrap']);
        $pdf->MultiCell($config['width'], $lineHeight, $wrappedValue, 0);
    }
}




public function update(Request $request)
{
    $validated = $request->validate([
        'edit_fets_id'       => 'required|integer|exists:fets_documents,id',
        'selected'           => 'required|array|min:1|max:5',
        'transfer_movement'  => 'required|string',
        'remarks'            => 'required|string',
        'repair_destination' => 'nullable|string|exists:repair_destinations,name',
        'to_receiver'        => 'nullable|string', // ✅ ADDED: Missing validation for to_receiver
    ]);

    $user = auth()->user();
    $fets = FetsDocument::where('id', $validated['edit_fets_id'])
        ->where('user_id', $user->id)
        ->where('status', 'submitted')
        ->firstOrFail();

    // Delete the old PDF file
    if ($fets->file_path && Storage::exists($fets->file_path)) {
        Storage::delete($fets->file_path);
    }

    // Determine receiver based on updated selection
    $movement = $validated['transfer_movement'];
    $remarks = $validated['remarks'];

    // Ensure repair_destination key exists to avoid undefined array key error
    $validatedWithDefaults = array_merge($validated, [
        'repair_destination' => $validated['repair_destination'] ?? null
    ]);

    $toPerson = $this->determineReceiver($user, $movement, $remarks, $validatedWithDefaults);

    // ✅ FIXED: Use a database transaction to ensure atomicity
    DB::beginTransaction();
    
    try {
        // Create a temporary request for PDF generation
        $tempRequest = new Request([
            'selected' => $validated['selected'],
            'transfer_movement' => $movement,
            'remarks' => $remarks,
            'repair_destination' => $validated['repair_destination'] ?? null,
            'embed' => '1',
            'is_update' => true // Flag to indicate this is an update operation
        ]);

        // Temporarily switch the request context
        $originalRequest = request();
        app()->instance('request', $tempRequest);

        // Call generate to create PDF - this will create a temporary FETS
        $result = $this->generate($tempRequest);

        // Restore original request
        app()->instance('request', $originalRequest);

        // Get the newly created FETS (last one for this user, excluding the one being edited)
        $newFets = FetsDocument::where('user_id', $user->id)
            ->where('id', '!=', $fets->id)
            ->latest('created_at')
            ->first();

        if ($newFets) {
            // Copy the new PDF file info to the original FETS
            $fets->update([
                'property_no' => $newFets->property_no,
                'to_receiver' => $newFets->to_receiver,
                'remarks' => $newFets->remarks,
                'transfer_movement' => $newFets->transfer_movement,
                'repair_destination' => $newFets->repair_destination,
                'file_name' => $newFets->file_name,
                'file_path' => $newFets->file_path,
                'updated_at' => now(),
            ]);

            // ✅ IMPORTANT: Delete the temporary FETS record immediately
            $newFets->delete();
            
            // Commit the transaction
            DB::commit();
        } else {
            throw new \Exception('Failed to generate new PDF - no temporary FETS created');
        }

    } catch (\Exception $e) {
        // Rollback the transaction on error
        DB::rollBack();
        
        // Restore original request
        app()->instance('request', $originalRequest);

        // Log the error for debugging
        Log::error('FETS Update Error: ' . $e->getMessage());

        // Fallback: update data without PDF regeneration
        $fets->update([
            'property_no' => implode(',', $validated['selected']),
            'to_receiver' => $toPerson,
            'remarks' => ($movement === 'For Repair') ? 'Repair' : $remarks,
            'transfer_movement' => $movement,
            'repair_destination' => ($movement === 'For Repair') ? $validated['repair_destination'] : null,
            'updated_at' => now(),
        ]);
    }

    // Log the update
    FetsLog::create([
        'property_no' => $fets->property_no,
        'action' => 'updated',
        'actor' => $user->fullname,
        'actor_role' => $user->access_level,
        'remarks' => "FETS #{$fets->id} updated by {$user->fullname}",
    ]);

    // For edit updates, check if request is from iframe (embed)
    if ($request->has('embed')) {
        // Return JSON for iframe to handle page refresh
        return response()->json([
            'success' => true,
            'message' => 'FETS Request has been updated successfully!',
            'redirect' => true
        ]);
    }

    // Regular redirect with success message
    return back()->with('success', 'FETS Request has been updated successfully!');
}









private function determineReceiver($user, string $movement, string $remarks, array $validated): string
{
    $province = strtolower(trim($user->province ?? ''));

    switch ($movement) {
        case 'Return to Lender':
        case 'For Surrender':
            if (strtolower($remarks) === 'serviceable') {
                $provincialDpsc = $this->getProvincialDpsc($province);
                if (!$provincialDpsc) {
                    throw ValidationException::withMessages([
                        'receiver' => "No active Provincial DPSC assigned for {$user->province}."
                    ]);
                }
                return $provincialDpsc->fullname;
            }

            $headOfProperty = $this->getHeadOfProperty();
            if (!$headOfProperty) {
                throw ValidationException::withMessages([
                    'receiver' => 'No active Head of Property found.'
                ]);
            }
            return $headOfProperty->fullname;

        case 'For Repair':
            if (empty($validated['repair_destination'])) {
                throw ValidationException::withMessages([
                    'repair_destination' => 'Repair destination must be specified.'
                ]);
            }
            return $validated['repair_destination'];

        case 'For Reissuance':
            if (!in_array($user->access_level, ['Provincial DPSC', 'Regional DPSC'])) {
                throw new \Exception('Unauthorized transfer movement for non-DPSC users.');
            }
            if (empty($validated['to_receiver'])) {
                throw ValidationException::withMessages([
                    'to_receiver' => 'Receiver must be specified for reissuance.'
                ]);
            }
            return $validated['to_receiver'];

        default:
            if (empty($validated['to_receiver'])) {
                throw ValidationException::withMessages([
                    'to_receiver' => 'Receiver must be specified.'
                ]);
            }
            return $validated['to_receiver'];
    }
}

/**
 * Helper: Get Provincial DPSC for given province
 */
private function getProvincialDpsc(string $province): ?\App\Models\Official
{
    return \App\Models\Official::where('role', 'Provincial DPSC')
        ->whereRaw('LOWER(province) = ?', [$province])
        ->where('active', true)
        ->first();
}

/**
 * Helper: Get Head of Property
 */
private function getHeadOfProperty(): ?\App\Models\Official
{
    return \App\Models\Official::where('role', 'Head of Property')
        ->where('active', true)
        ->first();
}




    // Helper functions
    private function wrapCommaText($text, $chunkLength = 17)
    {
        if (str_contains($text, ',')) {
            $parts = explode(',', $text);
            return implode("\n", array_map(
                fn($p) => wordwrap(trim($p), $chunkLength, "\n", true),
                $parts
            ));
        }

        return wordwrap($text, $chunkLength, "\n", true);
    }

    private function wrapDescription($text, $chunkLength = 180)
    {
        return wordwrap($text, $chunkLength, "\n", true);
    }

    private function wrapPersonOffice($text, $chunkLength = 29)
    {
        return wordwrap($text, $chunkLength, "\n", true);
    }





public function reviewSubmitted()
{
    $documents = FetsDocument::where('status', 'submitted')
        ->with('submitter') // ✅ Load the submitter relationship
        ->orderByDesc('created_at')
        ->paginate(10);

    return view('adminDPSC.Provincial.FETSrequest', compact('documents'));
}

    // ✅ View for Provincial to see already VERIFIED FETS
public function showVerified(Request $request)
{
    $query = FetsDocument::where('status', 'verified')
        ->with('submitter'); // ✅ Load the submitter relationship

    if ($request->has('date_filter')) {
        switch ($request->date_filter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                break;
        }
    }

    $documents = $query->latest()->paginate(10)->appends($request->all());

    return view('adminDPSC.Provincial.VerifiedFETS', compact('documents'));
}




public function submittedFets()
{
    $user = auth()->user();
    $documents = FetsDocument::where('user_id', $user->id)
        ->with(['submitter', 'verifier', 'approver'])
        ->orderByDesc('created_at')
        ->paginate(10);

    return view('SubmittedFETS', compact('documents'));
}

public function showForApproval(Request $request)
{
    $query = FetsDocument::where('status', 'approved');

    if ($request->has('date_filter')) {
        switch ($request->date_filter) {
            case 'today':
                $query->whereDate('updated_at', today());
                break;
            case 'week':
                $query->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year);
                break;
        }
    }

    $documents = $query->latest('updated_at')->paginate(10)->appends($request->all());

    return view('adminDPSC.Regional.ApprovedFETS', compact('documents'));
}



public function viewVerifiedFETS()
{
    $documents = FetsDocument::where('status', 'verified')->paginate(10);
    return view('adminDPSC.Provincial.VerifiedFETS', compact('documents'));
}



public function showVerifiedRegional(Request $request)
{
    $query = FetsDocument::where('status', 'verified')
        ->with('submitter'); // ✅ Load the submitter relationship

    if ($request->has('date_filter')) {
        switch ($request->date_filter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                break;
        }
    }

    $documents = $query->latest()->paginate(10)->appends($request->all());

    return view('adminDPSC.Regional.VerifiedFETS', compact('documents'));
}



public function verify($id)
{
    $fets = FetsDocument::findOrFail($id);

    if ($fets->status !== 'submitted') {
        return back()->with('error', 'Only submitted FETS can be verified.');
    }

    $fets->status = 'verified';
    $fets->verified_by = auth()->id(); // optional: add this if your table has it
    $fets->save();

 FetsLog::create([
     'property_no' => $fets->property_no,
     'action'      => 'verified',
     'actor'       => auth()->user()->fullname,
     'actor_role'  => auth()->user()->access_level,
     'remarks'     => "FETS #{$fets->id} verified by DPSC",
 ]);

    // Spatie activity log
    activity()
        ->causedBy(auth()->user())
        ->performedOn($fets)
        ->withProperties([
            'fets_id'      => $fets->id,
            'property_no' => $fets->property_no
        ])
        ->log('Verified FETS');

    return back()->with('success', 'FETS document verified successfully.');
}


public function approve($id)
{
    $fets = FetsDocument::findOrFail($id);

    if ($fets->status !== 'verified') {
        return back()->with('error', 'Only verified FETS can be approved.');
    }

    $fets->status = 'approved';
    $fets->approved_by = auth()->id();
    $fets->save();

    $propertyNumbers = array_map('trim', explode(',', $fets->property_no));

    // 🔹 Return-from-Repair Flow
    if (($fets->form_data['return_type'] ?? null) === 'from_repair') {
        // ... (This logic is correct from our previous fixes)
        foreach ($propertyNumbers as $propNo) {
            $cleanedPropNo = preg_replace('/\s+/', '', $propNo);
            $originalReceiver = ($fets->form_data['original_receivers'] ?? [])[$cleanedPropNo] ?? DB::table('inventory')->whereRaw("REPLACE(TRIM(PROPERTY_NO),' ','') = ?", [$cleanedPropNo])->value('RECEIVER');
            $repairDestination = ($fets->form_data['repair_destinations'] ?? [])[$cleanedPropNo] ?? 'Unknown';

            DB::table('inventory')
                ->whereRaw("REPLACE(TRIM(PROPERTY_NO),' ','') = ?", [$cleanedPropNo])
                ->update([
                    'STATUS' => null, 'RECEIVER' => $originalReceiver,
                    'DPO_REMARKS' => "Returned from Repair: {$repairDestination}",
                    'updated_at' => now(),
                ]);
        }

        $originalFetsToClose = FetsDocument::where('transfer_movement', 'For Repair')->where('status', 'approved')
            ->where(function ($query) use ($propertyNumbers) {
                foreach ($propertyNumbers as $propNo) {
                    $query->orWhere('property_no', 'like', "%{$propNo}%");
                }
            })->get();

        foreach ($originalFetsToClose as $originalFets) {
            $originalFets->status = 'completed';
            $originalFets->save();
        }

        $fets->status = 'completed';
        $fets->save();

        FetsLog::create([/* ... */]);
        return back()->with('success', 'Return-from-Repair FETS approved and inventory updated.');
    }

    // 🔹 Normal FETS Approval Flow

    // ✅ --- THIS IS THE FIX --- ✅
    // We removed the DPSC lookup. The receiver is now ALWAYS the person selected in the form.
    $receiverName = $fets->to_receiver;

    foreach ($propertyNumbers as $propNo) {
        $cleanedPropNo = preg_replace('/\s+/', '', $propNo);
        if ($fets->transfer_movement === 'For Repair') {
            DB::table('inventory')->whereRaw("REPLACE(TRIM(PROPERTY_NO),' ','') = ?", [$cleanedPropNo])
                ->update([
                    'STATUS' => 'Being Assessed for Repair',
                    'DPO_REMARKS' => "Assigned for Repair to: {$fets->repair_destination}",
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('inventory')->whereRaw("REPLACE(TRIM(PROPERTY_NO),' ','') = ?", [$cleanedPropNo])
                ->update(['RECEIVER' => $receiverName, 'updated_at' => now()]);
        }
    }

    $standardMovements = ['Issue/Transfer', 'For Surrender', 'Return to Lender'];
    if (in_array($fets->transfer_movement, $standardMovements)) {
        $fets->status = 'completed';
        $fets->save();
    }

    FetsLog::create([
        'property_no' => $fets->property_no,
        'action' => 'approved',
        'actor' => auth()->user()->fullname,
        'actor_role' => auth()->user()->access_level,
        'remarks' => ($fets->transfer_movement === 'For Repair')
            ? "FETS #{$fets->id} approved for repair assessment to {$fets->repair_destination}"
            : "FETS #{$fets->id} approved by DPSC, assigned to {$receiverName}",
    ]);
    return back()->with('success', 'FETS document approved successfully.');
}


public function reject(Request $request, $id)
{
    $fets = FetsDocument::findOrFail($id);

    if ($fets->status !== 'submitted' && $fets->status !== 'verified') {
        return back()->with('error', 'Only submitted or verified FETS can be rejected.');
    }

    $request->validate([
        'remarks' => 'required|string|max:1000',
    ]);

    $fets->status = 'rejected';
    $fets->rejected_remarks = $request->remarks;
    $fets->save();

    $propertyNumbers = array_map('trim', explode(',', $fets->property_no));

    // 🔹 If Return-from-Repair FETS is rejected → restore previous inventory values
    if (($fets->form_data['return_type'] ?? null) === 'from_repair') {
        $originalReceivers = $fets->form_data['original_receivers'] ?? [];
        $previousStatus    = $fets->form_data['previous_status'] ?? [];
        $previousRemarks   = $fets->form_data['previous_remarks'] ?? [];

        foreach ($propertyNumbers as $propNo) {
            $cleanedPropNo = preg_replace('/\s+/', '', $propNo);

            $originalReceiver = $originalReceivers[$propNo] ?? DB::table('inventory')
                ->whereRaw("REPLACE(TRIM(PROPERTY_NO), ' ', '') = ?", [$cleanedPropNo])
                ->value('RECEIVER');

            $statusToRestore  = $previousStatus[$propNo] ?? null;
            $remarksToRestore = $previousRemarks[$propNo] ?? null;

            DB::table('inventory')
                ->whereRaw("REPLACE(TRIM(PROPERTY_NO), ' ', '') = ?", [$cleanedPropNo])
                ->update([
                    'STATUS'      => $statusToRestore,
                    'RECEIVER'    => $originalReceiver,
                    'DPO_REMARKS' => $remarksToRestore,
                    'updated_at'  => now(),
                ]);
        }
    }

    // Log rejection
    FetsLog::create([
        'property_no' => $fets->property_no,
        'action'      => 'rejected',
        'actor'       => auth()->user()->fullname,
        'actor_role'  => auth()->user()->access_level,
        'remarks'     => "FETS #{$fets->id} rejected: " . $request->remarks,
    ]);

    return back()->with('success', 'FETS document rejected successfully.');
}

public function download($id)
{
    $doc = FetsDocument::findOrFail($id);
    $user = auth()->user();


    $allowed = $user->id === $doc->user_id || in_array($user->access_level, ['superadmin', 'Provincial DPSC', 'Regional DPSC']);

    if (!$allowed) {
        abort(403, 'Unauthorized access');
    }

    $filePath = storage_path("app/{$doc->file_path}");

    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }

    return response()->download($filePath, $doc->file_name);
}

public function preview($id)
{
    $doc = FetsDocument::findOrFail($id);
    $user = auth()->user();

    $allowed = $user->id === $doc->user_id || in_array($user->access_level, ['Superadmin', 'Provincial DPSC', 'Regional DPSC']);

    if (!$allowed) {
        abort(403, 'Unauthorized access');
    }

    // Check if file_path exists in database
    if (!$doc->file_path) {
        return response()->view('errors.pdf-not-available', [
            'message' => 'No PDF file path found in database. The document may not have been properly generated.',
            'doc' => $doc
        ], 404);
    }

    $filePath = storage_path("app/{$doc->file_path}");

    // Check if file needs to be regenerated
    $needsRegeneration = false;

    if (!file_exists($filePath)) {
        $needsRegeneration = true;
    } else {
        // Check if file is older than the last update to the document
        $fileModifiedTime = filemtime($filePath);
        $documentUpdatedTime = strtotime($doc->updated_at);

        if ($documentUpdatedTime > $fileModifiedTime) {
            $needsRegeneration = true;
        }
    }

    // Regenerate PDF if needed
    if ($needsRegeneration) {
        try {
            // Delete old file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Only allow PDF regeneration for document owners, admins, or superadmins
            $canRegenerate = ($user->id === $doc->user_id) ||
                           ($user->access_level === 'Superadmin') ||
                           ($user->access_level === 'Regional DPSC') ||
                           ($user->access_level === 'Provincial DPSC');

            if ($canRegenerate) {
                // Try to regenerate the PDF using the current FETS data
                $this->regeneratePdfForPreview($doc);

                // Update file path after regeneration
                $doc->refresh();
                $filePath = storage_path("app/{$doc->file_path}");
            }

            // Check again if file now exists
            if (!file_exists($filePath)) {
                if (!$canRegenerate) {
                    return response()->view('errors.pdf-not-available', [
                        'message' => 'PDF preview is temporarily unavailable. The document owner may need to regenerate the PDF.',
                        'doc' => $doc
                    ], 404);
                }

                // Debug information
                $debugInfo = [
                    'file_path_from_db' => $doc->file_path,
                    'full_file_path' => $filePath,
                    'file_exists' => file_exists($filePath),
                    'storage_path' => storage_path('app'),
                    'user_access_level' => $user->access_level,
                    'can_regenerate' => $canRegenerate,
                    'doc_id' => $doc->id
                ];

                return response()->view('errors.pdf-not-available', [
                    'message' => 'PDF file not found and could not be regenerated. Debug info: ' . json_encode($debugInfo),
                    'doc' => $doc
                ], 404);
            }
        } catch (\Exception $e) {
            $canRegenerate = ($user->id === $doc->user_id) ||
                           ($user->access_level === 'Superadmin') ||
                           ($user->access_level === 'Regional DPSC') ||
                           ($user->access_level === 'Provincial DPSC');
            if (!$canRegenerate) {
                return response()->view('errors.pdf-not-available', [
                    'message' => 'PDF preview failed: ' . $e->getMessage(),
                    'doc' => $doc
                ], 404);
            }

            // Debug information for regeneration failure
            $debugInfo = [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'file_path_from_db' => $doc->file_path,
                'full_file_path' => $filePath,
                'user_access_level' => $user->access_level,
                'doc_id' => $doc->id
            ];

            return response()->view('errors.pdf-not-available', [
                'message' => 'PDF regeneration failed: ' . $e->getMessage() . '. Debug info: ' . json_encode($debugInfo),
                'doc' => $doc
            ], 404);
        }
    }

    return response()->file($filePath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $doc->file_name . '"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ]);
}

private function regeneratePdfForPreview($doc)
{
    // For preview purposes, if the PDF file doesn't exist, we'll create a placeholder
    // or try to regenerate using a simplified approach

    // First, let's try to create the directory if it doesn't exist
    $filePath = storage_path("app/{$doc->file_path}");
    $directory = dirname($filePath);

    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }

    // Try to regenerate using the original user context
    $originalUser = auth()->user();
    $docOwner = $doc->submitter;

    if ($docOwner) {
        // Temporarily switch to the document owner's context
        auth()->login($docOwner);

        try {
            // Get the property numbers from the document
            $propertyNos = array_map('trim', explode(',', $doc->property_no));

            // Create a temporary request
            $tempRequest = new Request([
                'selected' => $propertyNos,
                'transfer_movement' => $doc->transfer_movement,
                'remarks' => $doc->remarks,
                'repair_destination' => $doc->repair_destination,
                'to_receiver' => $doc->to_receiver, // ✅ ADDED: Include to_receiver from the document
                'embed' => '1'
            ]);

            // Temporarily switch the request context
            $originalRequest = request();
            app()->instance('request', $tempRequest);

            try {
                // Call generate to create PDF
                $this->generate($tempRequest);

                // Get the newly created FETS (last one for this user)
                $newFets = FetsDocument::where('user_id', $doc->user_id)
                    ->latest('created_at')
                    ->first();

                if ($newFets && $newFets->id !== $doc->id) {
                    // Update the original FETS with new file info
                    $doc->update([
                        'file_name' => $newFets->file_name,
                        'file_path' => $newFets->file_path,
                    ]);

                    // Delete the temporary FETS record (but keep the PDF file)
                    $newFets->delete();
                } else {
                    throw new \Exception('No new FETS document was created');
                }

            } finally {
                // Restore original request
                app()->instance('request', $originalRequest);
            }

        } finally {
            // Restore original user
            auth()->login($originalUser);
        }
    } else {
        throw new \Exception('Document owner not found');
    }
}


}
