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
use App\Models\User;
use Illuminate\Database\QueryException;


class FetsController extends Controller
{

// FetsController.php

    public function select(Request $request)
    {
        $user = auth()->user();

        $receivers = DB::table('inventory')
            ->select('RECEIVER')
            ->distinct()
            ->where('RECEIVER', '!=', $user->fullname)
            ->orderBy('RECEIVER', 'asc')
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

        // 🔹 General lock list for all items in a pending FETS (excluding returns)
        $inProcessPropertyNos = DB::table('fets_items')
            ->join('fets_documents', 'fets_items.fets_document_id', '=', 'fets_documents.id')
            ->whereIn('fets_documents.status', ['submitted', 'verified', 'approved'])
            ->where('fets_documents.transfer_movement', '!=', 'Return from Repair')
            ->pluck('fets_items.property_no')
            ->map(fn($v) => trim($v))
            ->unique()
            ->toArray();

        // 🔹 Specific lock list for items in a "For Repair" FETS
        $repairInProcessPropertyNos = DB::table('fets_items')
            ->join('fets_documents', 'fets_items.fets_document_id', '=', 'fets_documents.id')
            ->whereIn('fets_documents.status', ['submitted', 'verified', 'approved'])
            ->where('fets_documents.transfer_movement', 'For Repair')
            ->pluck('fets_items.property_no')
            ->map(fn($v) => trim($v))
            ->unique()
            ->toArray();

        // --- ADDED: Determine Max Items ---
        // Fetch all items for the current user to check for long descriptions etc.
        $userInventoryForCheck = DB::table('inventory')->where('RECEIVER', $user->fullname)->get();
        // Define the check function
        $isLongCheck = fn($items) => $items->contains(fn($item) =>
            strlen($item->GENERAL_DESCRIPTION ?? '') > 180 || strlen($item->PROPERTY_NO ?? '') > 40 ||
            strlen($item->SERIAL_NO ?? '') > 60 || strlen($item->PAR_NO ?? '') > 60 ||
            strlen($item->RECEIVER ?? '') > 35 // Base check
        );
        // Determine if any item qualifies as 'long'
        $useLong = $isLongCheck($userInventoryForCheck);
        // Set the max items based on whether a long template would be needed
        $maxItems = $useLong ? 12 : 15;
        // --- END ADDED ---

        // Handle pagination vs "Show All"
        $perPage = $request->input('per_page', session('per_page', 10));
        session(['per_page' => $perPage]);

        $inventoryQuery = DB::table('inventory') // Renamed to avoid conflict
        ->where('RECEIVER', $user->fullname);

        // Exclude unserviceable items and items being repaired from FETS creation
        // Even Head of Property and DPSCs cannot FETS unserviceable items
        $inventoryQuery->where(function($q) {
            // Only show items that are available for transfer
            $q->whereNull('STATUS')
              ->orWhereNotIn('STATUS', ['Unserviceable', 'Being Assessed for Repair']);
        });

        // 🔎 Search by description, property no, or serial no
        if ($request->filled('search')) {
            $search = $request->search;
            $inventoryQuery->where(function ($query) use ($search) {
                $query->where('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                    ->orWhere('PROPERTY_NO', 'like', "%{$search}%")
                    ->orWhere('SERIAL_NO', 'like', "%{$search}%");
            });
        }

        if ($perPage === 'all') {
            $inventory = $inventoryQuery->orderBy('PROPERTY_NO')->get();
        } else {
            $inventory = $inventoryQuery->orderBy('PROPERTY_NO')
                ->paginate($perPage)
                ->appends($request->except('page'));
        }

        // Handle AJAX request
        if ($request->ajax() || $request->has('ajax')) {
            $html = '';

            // Determine items collection based on pagination type
            $itemsToLoop = ($inventory instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $inventory->items() : $inventory;

            foreach ($itemsToLoop as $item) { // Loop through the correct collection
                $disabledGeneral = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []);
                $disabledRepair = in_array($item->PROPERTY_NO, $repairInProcessPropertyNos ?? [])
                    && !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
                $isReturnedFromRepair = in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);

                $rowClass = ($disabledGeneral || $disabledRepair) ? 'bg-gray-100 text-gray-500 italic' : '';

                $html .= "<tr class='{$rowClass}'>";
                $html .= "<td class='p-2 text-center'>";

                if ($disabledRepair) {
                    $html .= '<span class="text-xs inline-block bg-orange-100 text-orange-700 px-2 py-1 rounded">Being Assessed for Repair</span>';
                } elseif ($disabledGeneral) {
                    $html .= '<span class="text-xs inline-block bg-yellow-100 text-yellow-700 px-2 py-1 rounded">FETS in Process</span>';
                } else {
                    $html .= '<input type="checkbox" name="selected[]" value="' . $item->PROPERTY_NO . '" class="select-checkbox">';
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

            // Include pagination links in AJAX response if needed, or handle separately in JS
            $paginationHtml = '';
            if ($inventory instanceof \Illuminate\Pagination\LengthAwarePaginator && $inventory->hasPages()) {
                $paginationHtml = $inventory->links()->toHtml();
            }

            return response()->json(['html' => $html, 'pagination' => $paginationHtml]); // Optionally return pagination
        }

        // ✅ Fetch repair destinations
        $repairDestinations = \App\Models\RepairDestination::all();

        // ✅ Get Officials
        $normalizedProvince = strtolower(trim($user->province ?? ''));
        $provincialOfficial = \App\Models\Official::where('role', 'Provincial DPSC')
            ->whereRaw('LOWER(province) = ?', [$normalizedProvince])
            ->where('active', true)
            ->first();
        $headOfProperty = \App\Models\Official::where('role', 'Head of Property')
            ->where('active', true)
            ->first();

        $provincialDisplay = $provincialOfficial
            ? "Provincial DPSC - {$provincialOfficial->fullname}"
            : "Provincial DPSC - Not Assigned";
        $headOfPropertyDisplay = $headOfProperty
            ? "Head of Property - {$headOfProperty->fullname}"
            : "Head of Property - Not Assigned";

        // ✅ Edit Mode Handling (for Employee users)
        $editFets = null;
        $prefilledData = [];
        $editingFetsPropertyNos = [];
        if ($request->has('edit')) {
            $editFets = FetsDocument::where('id', $request->edit)
                ->where('user_id', $user->id)
                ->where('status', 'submitted')
                ->first();

            if ($editFets) {
                // Get property numbers from the junction table
                $selectedPropertyNos = DB::table('fets_items')
                    ->where('fets_document_id', $editFets->id)
                    ->pluck('property_no')
                    ->toArray();
                
                $prefilledData = [
                    'transfer_movement' => $editFets->transfer_movement,
                    'remarks' => $editFets->remarks,
                    'repair_destination' => $editFets->repair_destination,
                    'selected_items' => $selectedPropertyNos,
                    'to_receiver' => $editFets->to_receiver,
                ];
                $editingFetsPropertyNos = $selectedPropertyNos;
            }
        }

        // Pass the new $maxItems variable
        return view('FETS', compact(
            'receivers',
            'allEquipment',
            'inventory',
            'inProcessPropertyNos',
            'repairInProcessPropertyNos',
            'returnedFromRepairPropNos',
            'repairDestinations',
            'provincialDisplay',
            'headOfPropertyDisplay',
            'maxItems',
            'editFets',
            'prefilledData',
            'editingFetsPropertyNos'
        ));
    }




// FetsController.php

// FetsController.php

// FetsController.php

    public function selectEmbed(Request $request)
    {
        $user = auth()->user();

        // Get receivers for dropdown - exclude current user
        $receivers = DB::table('inventory')->select('RECEIVER')->distinct()->where('RECEIVER', '!=', $user->fullname)->orderBy('RECEIVER', 'asc')->pluck('RECEIVER');

        // All equipment for "Show All" option
        $allEquipment = DB::table('inventory')->select('PROPERTY_NO', 'GENERAL_DESCRIPTION')->get();

        // 🔹 Units that have been returned from repair
        $returnedFromRepairPropNos = DB::table('inventory')
            ->whereNotNull('DPO_REMARKS')
            ->where('DPO_REMARKS', 'like', 'Returned from Repair:%')
            ->pluck('PROPERTY_NO')
            ->map(fn($v) => trim($v))
            ->toArray();

        // 🔹 General lock list for all items in a pending FETS
        $inProcessPropertyNos = DB::table('fets_items')
            ->join('fets_documents', 'fets_items.fets_document_id', '=', 'fets_documents.id')
            ->whereIn('fets_documents.status', ['submitted', 'verified', 'approved'])
            ->where('fets_documents.transfer_movement', '!=', 'Return from Repair') // Ignore completed returns
            ->pluck('fets_items.property_no')
            ->map(fn($v) => trim($v))
            ->unique()
            ->toArray();

        // 🔹 Specific lock list for items in a "For Repair" FETS
        $repairInProcessPropertyNos = DB::table('fets_items')
            ->join('fets_documents', 'fets_items.fets_document_id', '=', 'fets_documents.id')
            ->whereIn('fets_documents.status', ['submitted', 'verified', 'approved'])
            ->where('fets_documents.transfer_movement', 'For Repair')
            ->pluck('fets_items.property_no')
            ->map(fn($v) => trim($v))
            ->unique()
            ->toArray();

        // ✅ --- UPDATED THRESHOLDS TO MATCH generate() ---
        // Define the "isLong" check function using the correct limits from generate()
        $isItemLong = function($item) {
            // These thresholds MUST match the item-specific checks in generate()
            return strlen($item->GENERAL_DESCRIPTION ?? '') > 180 || // Correct threshold
                strlen($item->PROPERTY_NO ?? '') > 40 ||         // Correct threshold
                strlen($item->SERIAL_NO ?? '') > 60 ||           // Correct threshold
                strlen($item->PAR_NO ?? '') > 60 ||             // Correct threshold
                strlen($item->RECEIVER ?? '') > 35;              // Correct threshold
        };
        // ✅ --- END UPDATE ---

        // Define thresholds to pass to JS (these should also match generate)
        $longCheckThresholds = [
            'description' => 180, 'property_no' => 40, 'serial_no'   => 60,
            'par_no'      => 60, 'receiver'    => 35, 'remarks'     => 50, // remarks threshold for JS
        ];


        // Per page setting
        $perPage = $request->input('per_page', session('per_page', 10));
        session(['per_page' => $perPage]);

        // User inventory + search (only show items assigned to current user)
        $inventoryQuery = DB::table('inventory')
            ->where('RECEIVER', $user->fullname);

        // Exclude unserviceable items and items being repaired from FETS creation
        // Even Head of Property and DPSCs cannot FETS unserviceable items
        $inventoryQuery->where(function($q) {
            // Only show items that are available for transfer
            $q->whereNull('STATUS')
              ->orWhereNotIn('STATUS', ['Unserviceable', 'Being Assessed for Repair']);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $inventoryQuery->where(function ($query) use ($search) {
                $query->where('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                    ->orWhere('PROPERTY_NO', 'like', "%{$search}%")
                    ->orWhere('SERIAL_NO', 'like', "%{$search}%");
            });
        }

        // Handle pagination vs "Show All"
        if ($perPage === 'all') {
            $inventoryData = $inventoryQuery->orderBy('PROPERTY_NO')->get();
            // Manually create paginator for consistency if needed
            $inventory = new \Illuminate\Pagination\LengthAwarePaginator(
                $inventoryData,
                $inventoryData->count(),
                $inventoryData->count() ?: 1, // Avoid division by zero
                1, // Current page is 1
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $inventory = $inventoryQuery->orderBy('PROPERTY_NO')
                ->paginate((int)$perPage) // Cast perPage to int
                ->appends($request->query()); // Append all query params
        }


        // ✅ --- ADD 'is_long' flag to each item ---
        // Get the items collection (works for both paginator and manual 'all' paginator)
        $items = $inventory->getCollection();
        $items->transform(function ($item) use ($isItemLong) {
            $item->is_long = $isItemLong($item); // Add the boolean flag
            return $item;
        });
        // Update the paginator's items if it was paginated
        if ($inventory instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $inventory->setCollection($items);
        }
        // --- END 'is_long' flag ---


        // Handle AJAX request
        if ($request->ajax() || $request->has('ajax')) {
            $html = '';
            // Use the collection directly if 'all', otherwise use paginator's items
            $itemsToLoop = ($perPage === 'all') ? $items : $inventory->items();

            foreach ($itemsToLoop as $item) { // Ensure item has is_long property
                $disabledGeneral = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []);
                $disabledRepair = in_array($item->PROPERTY_NO, $repairInProcessPropertyNos ?? []) && !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
                $isReturnedFromRepair = in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
                $rowClass = ($disabledGeneral || $disabledRepair) ? 'bg-gray-100 text-gray-500 italic' : '';

                $html .= "<tr class='{$rowClass}'>";
                $html .= "<td class='p-2 text-center'>";
                if ($disabledRepair) {
                    $html .= '<span class="text-xs inline-block bg-orange-100 text-orange-700 px-2 py-1 rounded">Being Assessed for Repair</span>';
                } elseif ($disabledGeneral) {
                    $html .= '<span class="text-xs inline-block bg-yellow-100 text-yellow-700 px-2 py-1 rounded">FETS in Process</span>';
                } else {
                    // ✅ Add data-is-long attribute to checkbox HTML for AJAX
                    $html .= '<input type="checkbox" name="selected[]" value="' . $item->PROPERTY_NO . '" class="select-checkbox" data-is-long="' . ($item->is_long ? 'true' : 'false') . '">';
                    if ($isReturnedFromRepair) {
                        $html .= '<span class="block text-xs mt-1 text-green-700 font-semibold">(Returned from Repair)</span>';
                    }
                }
                $html .= "</td>";
                $html .= "<td class='p-2 text-center'>{$item->PROPERTY_NO}</td>";
                $html .= "<td class='p-2 text-center'>{$item->GENERAL_DESCRIPTION}</td>";
                $html .= "</tr>";
            }
            if (empty($html)) { $html = '<tr><td colspan="3" class="text-center p-2">No equipment available</td></tr>'; }

            // Optionally include pagination for AJAX updates
            $paginationHtml = '';
            if ($inventory instanceof \Illuminate\Pagination\LengthAwarePaginator && $inventory->hasPages()) {
                $paginationHtml = $inventory->links()->toHtml();
            }
            return response()->json(['html' => $html, 'pagination' => $paginationHtml]);
        }

        // Repair destinations & Officials
        $repairDestinations = \App\Models\RepairDestination::all();
        $normalizedProvince = strtolower(trim($user->province ?? ''));
        $provincialOfficial = \App\Models\Official::where('role', 'Provincial DPSC')->whereRaw('LOWER(province) = ?', [$normalizedProvince])->where('active', true)->first();
        $headOfProperty = \App\Models\Official::where('role', 'Head of Property')->where('active', true)->first();
        $provincialDisplay = $provincialOfficial ? "Provincial DPSC - {$provincialOfficial->fullname}" : "Provincial DPSC - Not Assigned";
        $headOfPropertyDisplay = $headOfProperty ? "Head of Property - {$headOfProperty->fullname}" : "Head of Property - Not Assigned";

        // Edit Mode Handling
        $editFets = null; $prefilledData = []; $editingFetsPropertyNos = [];
        if ($request->has('edit')) {
            $editFets = FetsDocument::where('id', $request->edit)
                ->where('user_id', $user->id)
                ->where('status', 'submitted')
                ->first();

            if ($editFets) {
                // Get property numbers from the junction table
                $selectedPropertyNos = DB::table('fets_items')
                    ->where('fets_document_id', $editFets->id)
                    ->pluck('property_no')
                    ->toArray();
                
                $prefilledData = [
                    'transfer_movement' => $editFets->transfer_movement,
                    'remarks' => $editFets->remarks,
                    'repair_destination' => $editFets->repair_destination,
                    'selected_items' => $selectedPropertyNos,
                    // Add receiver if needed for prefill
                    'to_receiver' => $editFets->to_receiver,
                ];
                $editingFetsPropertyNos = $selectedPropertyNos;
                
                // ✅ Ensure the to_receiver is in the receivers list for editing
                if ($editFets->to_receiver && !$receivers->contains($editFets->to_receiver)) {
                    $receivers->push($editFets->to_receiver);
                }
            }
        }

        config(['view.paths' => [resource_path('views')]]);

        // Pass inventory (now with 'is_long' flags) AND thresholds
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
            'longCheckThresholds' => $longCheckThresholds, // Pass thresholds to JS
        ]);
    }



// 2️⃣ Submit return FETS (creates new FETS document)
// FetsController.php

    public function submitReturnFets(Request $request)
    {
        $request->validate([
            'selected'   => 'required|array|min:1|max:15', // <-- UPDATED MAX TO 15
            'selected.*' => 'exists:inventory,PROPERTY_NO',
            'remarks'    => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        if ($user->access_level !== 'Provincial DPSC') {
            abort(403, 'Unauthorized.');
        }

        // --- ADDED: Determine Max Items dynamically BEFORE validation fails ---
        // Fetch selected items to check their descriptions/lengths
        $selectedUnitsForCheck = DB::table('inventory')->whereIn('PROPERTY_NO', $request->selected)->get();
        $isLongCheck = fn($items) => $items->contains(fn($item) =>
            strlen($item->GENERAL_DESCRIPTION ?? '') > 180 || strlen($item->PROPERTY_NO ?? '') > 40 ||
            strlen($item->SERIAL_NO ?? '') > 60 || strlen($item->PAR_NO ?? '') > 60 ||
            strlen($item->RECEIVER ?? '') > 35
        );
        $useLong = $isLongCheck($selectedUnitsForCheck);
        $maxItemsAllowed = $useLong ? 12 : 15;

        if (count($request->selected) > $maxItemsAllowed) {
            $errorMessage = $useLong
                ? "Maximum {$maxItemsAllowed} items allowed when item details require the long format. You selected " . count($request->selected) . "."
                : "Maximum {$maxItemsAllowed} items allowed for the standard format. You selected " . count($request->selected) . ".";
            return back()->withInput()->with('error', $errorMessage);
        }
        // --- END ADDED ---


        // Fetch selected inventory rows (already done above for check)
        $units = $selectedUnitsForCheck;
        if ($units->isEmpty()) {
            return back()->with('error', 'Selected equipment not found in inventory.');
        }

        // ... (Rest of the method remains the same - Fetching FETS, checking submitters, etc.)
        $fetsForRepair = FetsDocument::where('transfer_movement', 'For Repair')
            ->whereIn('status', ['submitted', 'verified', 'approved'])
            ->with('submitter') // Eager load
            ->get();

        $originalSubmitters = [];
        foreach ($units as $unit) {
            $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
                // Check if this property is in the FETS items
                return DB::table('fets_items')
                    ->where('fets_document_id', $f->id)
                    ->where('property_no', trim($unit->PROPERTY_NO))
                    ->exists();
            });
            $originalSubmitters[$unit->PROPERTY_NO] = $originalFets->submitter->fullname ?? null;
        }
        if (count(array_unique(array_values($originalSubmitters))) > 1) { // Fixed array_unique usage
            return back()->with('error', 'You cannot return units from different employees in a single FETS.');
        }

        $originalReceivers = []; $previousStatus = []; $previousRemarks = []; $repairDestinations = [];
        foreach ($units as $unit) {
            $cleanKey = preg_replace('/\s+/', '', $unit->PROPERTY_NO);
            $originalReceivers[$cleanKey] = $unit->RECEIVER;
            $previousStatus[$cleanKey]    = $unit->STATUS;
            $previousRemarks[$cleanKey]   = $unit->DPO_REMARKS;
            // Use a null-safe operator and correct logic for finding original FETS
            $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
                // Check if this property is in the FETS items
                return DB::table('fets_items')
                    ->where('fets_document_id', $f->id)
                    ->where('property_no', trim($unit->PROPERTY_NO))
                    ->exists();
            });
            $repairDestinations[$cleanKey] = $originalFets->repair_destination ?? 'Unknown';
        }
        $toReceiver = reset($originalReceivers) ?: null;
        if (!$toReceiver) { return back()->with('error', 'Receiver not found.'); }

        // ✅ --- THIS IS THE FIX ---
        // Replace the commented-out line with this:
        DB::table('inventory')
            ->whereIn('PROPERTY_NO', $request->selected)
            ->update([
                'STATUS'      => 'Pending Return',
                'DPO_REMARKS' => DB::raw("CONCAT(IFNULL(DPO_REMARKS, ''), ' | Pending Return')"),
                'updated_at'  => now(),
            ]);
        // ✅ --- END OF FIX ---

        // Generate FETS using fake request
        $fakeRequest = new Request([
            'selected' => $request->selected,
            'transfer_movement' => 'Return from Repair',
            'remarks' => $request->remarks ?? 'Returned from Repair',
            'to_receiver' => $toReceiver,
            'source' => 'return_form' // <-- ADD THIS LINE
        ]);

        try {
            // Call generate (which now has its own max item check and error handling)
            $pdfResult = $this->generate($fakeRequest);

            // Check if generate returned a redirect response with an error
            if ($pdfResult instanceof \Illuminate\Http\RedirectResponse && $pdfResult->getSession()->has('error')) {
                // If generate failed, pass the error back and trigger the catch block to revert
                throw new \Exception($pdfResult->getSession()->get('error'));
            }

            // Proceed if generate was successful
            $fets = FetsDocument::where('user_id', $user->id)->latest()->first();
            if (!$fets) { throw new \Exception('Failed to retrieve generated FETS document.'); }

            // Update form_data
            $formData = (array) ($fets->form_data ?? []);
            $formData = array_merge($formData, [
                'original_receivers'   => $originalReceivers, 'previous_status' => $previousStatus,
                'previous_remarks'     => $previousRemarks, 'repair_destinations' => $repairDestinations,
                'return_type'          => 'from_repair',
            ]);
            $fets->form_data = $formData;
            // Remarks for the FETS document itself, separate from PDF content
            $fets->remarks = $request->remarks ?? 'Returned from Repair';
            $fets->save();

            // Log the submission
            FetsLog::create([
                'property_no' => implode(',', $request->selected),
                'action'      => 'submitted',
                'actor'       => $user->fullname,
                'actor_role'  => $user->access_level,
                'remarks'     => "Return-from-Repair FETS #{$fets->id} submitted by {$user->fullname}",
            ]);

            if ($pdfResult instanceof \Illuminate\Http\RedirectResponse) {
                return $pdfResult->with('success', "Return FETS submitted (ID {$fets->id}).");
            } else {
                // Fallback redirect (should not be reached if generate works as expected)
                return back()->with([
                    'success'           => "Return FETS submitted (ID {$fets->id}).",
                    'fets_id'           => $fets->id,
                    'fets_preview_url'  => route('fets.preview', ['id' => $fets->id]),
                    'fets_download_url' => route('fets.download', ['id' => $fets->id]),
                ]);
            }

        } catch (\Exception $e) {
            // Revert inventory status if FETS generation or saving fails
            // Use the $previousStatus and $previousRemarks arrays we already built
            $statusCase = "";
            $remarksCase = "";
            foreach($request->selected as $pn) {
                $cleanKey = preg_replace('/\s+/', '', $pn);
                $status = $previousStatus[$cleanKey] ? "'".$previousStatus[$cleanKey]."'" : "NULL";
                $remarks = $previousRemarks[$cleanKey] ? "'".$previousRemarks[$cleanKey]."'" : "NULL";
                $statusCase .= " WHEN '{$pn}' THEN {$status}";
                $remarksCase .= " WHEN '{$pn}' THEN {$remarks}";
            }

            DB::table('inventory')->whereIn('PROPERTY_NO', $request->selected)->update([
                'STATUS'      => DB::raw("CASE PROPERTY_NO {$statusCase} END"),
                'DPO_REMARKS' => DB::raw("CASE PROPERTY_NO {$remarksCase} END"),
                'updated_at'  => now(),
            ]);

            Log::error("Return FETS submission failed: " . $e->getMessage());
            return back()->with('error', 'Failed to submit Return FETS: ' . $e->getMessage());
        }
    }

// 🔧 Return from Repair Page (Provincial DPSC)
// FetsController.php

// 🔧 Return from Repair Page (Provincial DPSC) - UPDATED
// FetsController.php

// 🔧 Return from Repair Page (Provincial DPSC) - UPDATED with Filters & Pagination
// FetsController.php

// FetsController.php

// 🔧 Return from Repair Page (Provincial DPSC) - UPDATED with Filters & is_long flag
    public function showReturnFromRepair(Request $request)
    {
        $user = auth()->user();

        if ($user->access_level !== 'Provincial DPSC') {
            abort(403, 'Unauthorized.');
        }

        // Fetch original "For Repair" FETS documents for this Provincial DPSC
        // Case 1: Employee submits → Provincial verifies (verified_by = this user)
        // Case 2: Regional DPSC submits → Provincial of same province verifies
        $fetsForRepair = FetsDocument::where('status', 'approved')
            ->where('transfer_movement', 'For Repair')
            ->where(function($q) use ($user) {
                // Case 1: This Provincial DPSC verified the FETS
                $q->where('verified_by', $user->id)
                  // Case 2: Regional DPSC from same province submitted the FETS
                  ->orWhereHas('submitter', function($subQ) use ($user) {
                      $subQ->where('access_level', 'Regional DPSC')
                           ->where('province', $user->province);
                  });
            })
            ->with('submitter') // Eager load submitter for efficiency
            ->get();

        // Get all unique property numbers eligible for return from the junction table
        $fetsIds = $fetsForRepair->pluck('id')->toArray();
        $propNos = DB::table('fets_items')
            ->whereIn('fets_document_id', $fetsIds)
            ->pluck('property_no')
            ->unique()
            ->values()
            ->toArray();

        // Base query for returnable units
        $returnableUnitsQuery = $propNos
            ? DB::table('inventory')
                ->whereIn('PROPERTY_NO', $propNos)
                ->where(function($q) { // Include items being assessed or pending return
                    $q->where('STATUS', 'Being Assessed for Repair')
                        ->orWhere('STATUS', 'Pending Return');
                })
                ->where(function($q) { // Exclude items already returned
                    $q->where('DPO_REMARKS', 'not like', 'Returned from Repair%')
                        ->orWhereNull('DPO_REMARKS');
                })
            : null; // Use null if no property numbers to query

        // Apply Search Filter if provided
        if ($returnableUnitsQuery && $request->filled('search')) {
            $search = $request->search;
            $returnableUnitsQuery->where(function ($query) use ($search) {
                $query->where('PROPERTY_NO', 'like', "%{$search}%")
                    ->orWhere('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                    ->orWhere('SERIAL_NO', 'like', "%{$search}%");
                // Note: Searching Original Owner efficiently might require joins or post-filtering
            });
        }

        // Handle Pagination Settings
        $perPage = $request->input('per_page', session('return_per_page', 10));
        session(['return_per_page' => $perPage]);
        $totalReturnableCount = $returnableUnitsQuery ? $returnableUnitsQuery->count() : 0; // Get total before pagination

        // Execute Query and Paginate (or create empty paginator)
        if ($returnableUnitsQuery) {
            if ($perPage === 'all') {
                $returnableUnitsCollection = $returnableUnitsQuery->orderBy('PROPERTY_NO')->get();
                // Manually create paginator for 'all' to ensure consistency in the view
                $returnableUnits = new \Illuminate\Pagination\LengthAwarePaginator(
                    $returnableUnitsCollection,
                    $totalReturnableCount,
                    $totalReturnableCount ?: 1, // Avoid division by zero
                    1, // Current page is 1
                    ['path' => $request->url(), 'query' => $request->query()] // Keep existing query params
                );
            } else {
                // Paginate normally and append all query parameters
                $returnableUnits = $returnableUnitsQuery->orderBy('PROPERTY_NO')->paginate((int)$perPage)->appends($request->query());
            }
        } else {
            // Create an empty Paginator instance if no query needed
            $returnableUnits = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), 0, (int)$perPage ?: 10, $request->input('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // Define isItemLong check function (matching generate method)
        $isItemLong = function($item) {
            return strlen($item->GENERAL_DESCRIPTION ?? '') > 180 ||
                strlen($item->PROPERTY_NO ?? '') > 40 ||
                strlen($item->SERIAL_NO ?? '') > 60 ||
                strlen($item->PAR_NO ?? '') > 60 ||
                strlen($item->RECEIVER ?? '') > 35;
        };

        // Determine original submitters AND add 'is_long' flag for the CURRENT PAGE items
        $unitSubmitters = [];
        $currentPageItems = collect($returnableUnits->items()); // Get collection for current page
        $currentPageItems->transform(function ($item) use ($isItemLong, $fetsForRepair, &$unitSubmitters) {
            // Add the is_long flag
            $item->is_long = $isItemLong($item);

            // Find original submitter by checking fets_items junction table
            $originalFets = $fetsForRepair->first(function ($f) use ($item) {
                // Check if this property number exists in this FETS document's items
                $hasPropNo = DB::table('fets_items')
                    ->where('fets_document_id', $f->id)
                    ->where('property_no', $item->PROPERTY_NO)
                    ->exists();
                return $hasPropNo;
            });
            $unitSubmitters[$item->PROPERTY_NO] = $originalFets->submitter->fullname ?? 'Unknown';

            return $item; // Return the modified item
        });
        // Update the paginator's collection with the modified items (including is_long flag)
        if ($returnableUnits instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $returnableUnits->setCollection($currentPageItems);
        }

        // Build locked property numbers list from junction table
        $lockedFetsIds = FetsDocument::whereIn('status', ['submitted','verified','approved'])
            ->where('transfer_movement', 'Return from Repair')
            ->pluck('id')
            ->toArray();
        
        $lockedPropNos = DB::table('fets_items')
            ->whereIn('fets_document_id', $lockedFetsIds)
            ->pluck('property_no')
            ->unique()
            ->toArray();

        // Return the view with all necessary data
        return view('adminDPSC.Provincial.ReturnFromRepair', [
            'returnableUnits' => $returnableUnits, // Now contains items with ->is_long
            'fetsForRepair'   => $fetsForRepair,
            'lockedPropNos'   => $lockedPropNos,
            'unitSubmitters'  => $unitSubmitters,
            // 'maxItems' is no longer passed; calculated by JS
            'totalReturnableCount' => $totalReturnableCount, // Pass total count for 'Show All'
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
            'selected'           => 'required|array|min:1|max:15',
            'transfer_movement'  => 'required|string',
            'remarks'            => 'required|string',
            'repair_destination' => 'nullable|string|exists:repair_destinations,name',
            'to_receiver'        => 'nullable|string',
        ]);

        // 2. Initial Data Gathering
        $user     = auth()->user();
        $movement = $validated['transfer_movement'];
        $finalRemarks = ($movement === 'For Repair') ? 'Repair' : $validated['remarks'];
        $toPerson = $this->determineReceiver($user, $movement, $validated['remarks'], $validated);
        $allItems = DB::table('inventory')->whereIn('PROPERTY_NO', $validated['selected'])->get();

        if ($allItems->isEmpty()) {
            return back()->with('error', 'Selected equipment not found.');
        }
        $itemCount = count($allItems);

        // ✅ NEW: Get the actual office from the current user.
        // Use the user's defined office, with a fallback.
        $userOffice = $this->getAbbreviatedOffice($user->office ?? 'Pantawid (RPMO)');
        $date = now()->format('F d, Y');

        // 3. Determine if Long Template is Needed
        $isLongCheck = fn($items) => $items->contains(fn($item) =>
            strlen($item->GENERAL_DESCRIPTION ?? '') > 180 || strlen($item->PROPERTY_NO ?? '') > 40 ||
            strlen($item->SERIAL_NO ?? '') > 60 || strlen($item->PAR_NO ?? '') > 60 ||
            strlen($item->RECEIVER ?? '') > 35 || strlen($toPerson) > 35 || strlen($validated['remarks']) > 50
        );
        $useLong = $isLongCheck($allItems);

        // 4. Conditional Max Items Check
        $maxItems = $useLong ? 12 : 15;
        if ($itemCount > $maxItems) {
            $errorMessage = $useLong
                ? "Maximum {$maxItems} items allowed when using the long template format due to content length. You selected {$itemCount}."
                : "Maximum {$maxItems} items allowed for the standard template format. You selected {$itemCount}.";
            return back()->withInput()->with('error', $errorMessage);
        }

        // 5. Determine Template Name and Config
        $templateName = 'FETS-FO-9' . ($useLong ? '-long' : '') . ($itemCount > 1 ? '-for' . $itemCount : '') . '.pdf';
        $configSet = config('fets_coords');
        $cfg = $configSet[$templateName] ?? null;

        if (!$cfg) {
            return back()->with('error', "No template config found for '{$templateName}'. Ensure templates for up to {$maxItems} items exist.");
        }

        $columnLayout = $configSet['item_columns'][($useLong ? 'long' : 'standard')];
        $lineHeight = $cfg['line_height'];

        // 6. PDF Generation Setup
        $pdf = new \setasign\Fpdi\Fpdi();
        $templatePath = storage_path("app/public/templates/{$templateName}");
        if (!Storage::disk('local')->exists("public/templates/{$templateName}")) {
            return back()->with('error', "Template file not found: {$templateName}");
        }
        $pageCount = $pdf->setSourceFile($templatePath);

        // 7. Two-Page Logic Setup
        $isLongTemplate = $useLong;
        $itemCountFromTemplate = $itemCount;

        $fieldsOnPage2 = []; // Start empty
        $signatureFields = ['requested_by', 'recommending', 'approving', 'received_by'];
        $toFields = ['to_office', 'to_person'];
        $fromFields = ['from_office', 'from_person'];

        if (!$isLongTemplate) {
            if ($itemCountFromTemplate == 15) {
                $fieldsOnPage2 = array_merge($signatureFields, $toFields);
            } elseif ($itemCountFromTemplate == 14) {
                $fieldsOnPage2 = $signatureFields;
            }
        } else {
            if ($itemCountFromTemplate >= 9 && $itemCountFromTemplate <= 12) {
                $fieldsOnPage2 = array_merge($signatureFields, $toFields, $fromFields);
            } elseif ($itemCountFromTemplate == 8) {
                $fieldsOnPage2 = $signatureFields;
            }
        }

        // 8. Data for Static Fields (Updated to use wrapPersonOffice helper)
        $staticData = [
            'fets_date'     => now()->format('F d, Y'),
            'from_office'   => $userOffice,     // ✅ Use dynamic user office
            'to_office'     => $userOffice,     // ✅ Use dynamic user office
            'from_person'   => $allItems->first()->RECEIVER ?? '',
            'to_person'     => $toPerson,
            'requested_by'  => $allItems->first()->RECEIVER ?? '',
            'received_by'   => $toPerson,
            'recommending'  => \App\Models\Official::where('role', 'Recommending')->where('active', true)->first()->fullname ?? 'N/A',
            'approving'     => \App\Models\Official::where('role', 'Approving')->where('active', true)->first()->fullname ?? 'N/A',
        ];

        // 9. Loop through pages and write PDF content
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
            $pdf->SetFont('Helvetica', '', 8);
            $pdf->SetTextColor(0, 0, 0);

            // Write Static Fields based on which page we are on
            foreach ($cfg['fields'] as $fieldName => $fieldConfig) {
                $isOnPage2 = in_array($fieldName, $fieldsOnPage2);
                if (isset($staticData[$fieldName])) {
                    if (($i === 1 && !$isOnPage2) || ($i === 2 && $isOnPage2)) {
                        $pdf->SetXY($fieldConfig['x'], $fieldConfig['y']);
                        $wrapLength = $fieldConfig['wrap'] ?? 30;
                        $fieldWidth = $fieldConfig['width'] ?? 40;

                        // ✅ Use wrapPersonOffice for all signatory/office fields
                        if (in_array($fieldName, ['from_office', 'to_office', 'from_person', 'to_person', 'requested_by', 'recommending', 'approving', 'received_by'])) {
                            $wrappedValue = $this->wrapPersonOffice($staticData[$fieldName], $fieldConfig['wrap']);
                        } else {
                            // Default to wrapDescription for FETS date and item details in case they are defined here
                            $wrappedValue = $this->wrapDescription($staticData[$fieldName], $fieldConfig['wrap']);
                        }

                        $pdf->MultiCell($fieldWidth, $lineHeight, $wrappedValue, 0);
                    }
                }
            }

            // 10. Write Item Rows using the helper (ONLY on the first page)
            if ($i === 1) {
                if ($itemCount === 1) {
                    $itemRowY = $cfg['item_row_y'] ?? 50;
                    $this->renderPdfItemRow($pdf, $allItems->first(), $itemRowY, $lineHeight, $finalRemarks, $columnLayout);
                } else {
                    $baseY = $cfg['base_y'] ?? 50;
                    $yOffset = $cfg['y_offset'] ?? 6;
                    foreach ($allItems as $idx => $item) {
                        $y = $baseY + ($yOffset * $idx);
                        $this->renderPdfItemRow($pdf, $item, $y, $lineHeight, $finalRemarks, $columnLayout);
                    }
                }
            }
        }

        // 11. Save PDF and Update/Create Database Record (with Error Handling)
        $fileName = 'fets_' . now()->format('Ymd_His') . '_' . $itemCount . 'items.pdf';
        $filePath = "public/fets/{$fileName}";
        Storage::put($filePath, $pdf->Output('S'));

        try {
            // Check if this is an edit operation
            $editFetsId = $request->input('edit_fets_id');
            
            if ($editFetsId) {
                // Update existing FETS
                $fets = FetsDocument::findOrFail($editFetsId);
                
                // Delete old PDF file if it exists
                if ($fets->file_path && Storage::exists($fets->file_path)) {
                    Storage::delete($fets->file_path);
                }
                
                // Update FETS record with new PDF
                $fets->update([
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'updated_at' => now(),
                ]);
            } else {
                // Create new FETS
                $fets = FetsDocument::create([
                    'to_receiver'        => $toPerson,
                    'to_office'          => $validated['to_office'] ?? 'Pantawid (RPMO)',
                    'remarks'            => $finalRemarks,
                    'transfer_movement'  => $movement,
                    'repair_destination' => ($movement === 'For Repair') ? $validated['repair_destination'] : null,
                    'user_id'            => $user->id,
                    'file_name'          => $fileName,
                    'file_path'          => $filePath,
                    'status'             => 'submitted',
                ]);
                
                // Add items to the junction table (only for new FETS, edit already updated items)
                foreach ($validated['selected'] as $propertyNo) {
                    DB::table('fets_items')->insert([
                        'fets_document_id' => $fets->id,
                        'property_no' => trim($propertyNo),
                        'item_status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

// 12. Redirect
// Check if the request originated from the 'Return from Repair' form
            if ($request->get('source') === 'return_form') {
                // If it's a Return from Repair, redirect back to the previous page (the form)
                // The calling controller (submitReturnFets) will handle the final redirect back.
                // We just need to make sure we return a success signal.

                // A simple return statement here works, as the calling method is set up
                // to handle this return value and issue the final redirect()->back().
                return [
                    'success'           => 'FETS submitted and PDF generated successfully.',
                    'fets_id'           => $fets->id,
                    'fets_preview_url'  => route('fets.preview', ['id' => $fets->id]),
                    'fets_download_url' => route('fets.download', ['id' => $fets->id]),
                ];

            } else {
                // For all other FETS movements (the default behavior)
                $redirectRoute = $request->has('embed') ? 'fets.select.embed' : 'fets.select';
                return redirect()->route($redirectRoute)->with([
                    'success'           => 'FETS submitted and PDF generated successfully.',
                    'fets_id'           => $fets->id,
                    'fets_preview_url'  => route('fets.preview', ['id' => $fets->id]),
                    'fets_download_url' => route('fets.download', ['id' => $fets->id]),
                ]);
            }

        } catch (QueryException $e) {
            Storage::delete($filePath);
            if ($e->getCode() === '22001' || str_contains($e->getMessage(), 'Data too long')) {
                return back()->withInput()->with('error', 'Too many items selected. The list of property numbers is too long to save (max ~15 items recommended due to database limits).');
            } else {
                Log::error("FETS creation failed: " . $e->getMessage());
                return back()->withInput()->with('error', 'An unexpected database error occurred while saving the FETS document.');
            }
        } catch (\Exception $e) {
            Storage::delete($filePath);
            Log::error("FETS creation failed: " . $e->getMessage());
            return back()->withInput()->with('error', 'An unexpected error occurred while saving the FETS document.');
        }
    }

// FetsController.php - Inside the class FetsController

    // ✅ ADDED: Map for converting long office names to short versions
    private $officeAbbreviations = [
        'PANTAWID PAMILYA PILIPINO PROGRAM DIVISION, PANTAWID (RPMO)' => 'Pantawid (RPMO)', // Example
        'MONKAYO MUNICIPAL OPERATIONS OFFICE' => 'Monkayo MOO',
        'COMPOSTELA MUNICIPAL OPERATIONS OFFICE' => 'Compostela MOO',
        'MACO MUNICIPAL OPERATIONS OFFICE' => 'Maco MOO',
        'NABUNTURAN (CAPITAL) MUNICIPAL OPERATIONS OFFICE' => 'Nabunturan MOO',
        // Add all your long office names here as keys
        // (This array needs to be fully populated with all long names from your DB)
        // For simplicity, let's use a placeholder for now:
        'LONG OFFICE NAME HERE' => 'SHORT OFFICE HERE',
        // Example DSWD office from general knowledge (if applicable)
        'DSWD FIELD OFFICE XI' => 'DSWD FO XI',
        // Ensure the full name is captured if you need it shortened
        // You will need to fully populate this list from your database
    ];

    /**
     * Helper: Converts long office names to abbreviations for PDF display.
     */
    private function getAbbreviatedOffice(string $officeName): string
    {
        // Use your existing User Controller map as a reference, or the explicit map above.
        // For now, let's try to grab a safe name if the standard name is long.
        $standardAbbr = [
            'PANTAWID PAMILYA PILIPINO PROGRAM DIVISION, PANTAWID (RPMO)' => 'Pantawid (RPMO)',
        ];

        // Normalize the input office name to match case/spaces in the map keys
        $normalizedOffice = trim(strtoupper($officeName));

        // Return the abbreviation if found, otherwise return the original name
        return $this->officeAbbreviations[$normalizedOffice] ?? $officeName;
    }

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
        'to_receiver'        => 'required_if:transfer_movement,Issue/Transfer|nullable|string',
        'repair_destination' => 'nullable|string|exists:repair_destinations,name',
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

    // Update FETS data
    $fets->update([
        'to_receiver' => $toPerson,
        'to_office' => $validated['to_office'] ?? 'Pantawid (RPMO)',
        'remarks' => ($movement === 'For Repair') ? 'Repair' : $remarks,
        'transfer_movement' => $movement,
        'repair_destination' => ($movement === 'For Repair') ? $validated['repair_destination'] : null,
        'updated_at' => now(),
    ]);
    
    // Update items in junction table
    DB::table('fets_items')->where('fets_document_id', $fets->id)->delete();
    foreach ($validated['selected'] as $propertyNo) {
        DB::table('fets_items')->insert([
            'fets_document_id' => $fets->id,
            'property_no' => trim($propertyNo),
            'item_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    // Regenerate PDF with updated data
    $tempRequest = new Request([
        'selected' => $validated['selected'],
        'transfer_movement' => $movement,
        'remarks' => $remarks,
        'repair_destination' => $validated['repair_destination'] ?? null,
        'to_receiver' => $toPerson,
        'edit_fets_id' => $fets->id, // Pass the FETS ID so generate knows to update this one
    ]);
    
    try {
        // Call generate to create new PDF
        $this->generate($tempRequest);
    } catch (\Exception $e) {
        // If PDF regeneration fails, log it but don't fail the update
        Log::warning("PDF regeneration failed for FETS #{$fets->id}: " . $e->getMessage());
    }

    // Log the update (get property numbers from junction table)
    $propertyNos = DB::table('fets_items')
        ->where('fets_document_id', $fets->id)
        ->pluck('property_no')
        ->join(',');
    
    FetsLog::create([
        'property_no' => $propertyNos ?: 'N/A',
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
    $user = auth()->user();
    
    // Provincial DPSC should only see FETS from their province
    $documents = FetsDocument::where('status', 'submitted')
        ->whereHas('submitter', function($q) use ($user) {
            $q->where('province', $user->province);
        })
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
    // Show both 'approved' and 'completed' status
    // (completed includes Issue/Transfer, For Surrender, Return to Lender that were auto-completed)
    $query = FetsDocument::whereIn('status', ['approved', 'completed'])
        ->whereNotNull('approved_by'); // Only show FETS that have been approved

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

 // Get property numbers from junction table for logging
    $logPropertyNos = DB::table('fets_items')
        ->where('fets_document_id', $fets->id)
        ->pluck('property_no')
        ->join(',');
    
    FetsLog::create([
     'property_no' => $logPropertyNos ?: 'N/A',
     'action'      => 'verified',
     'actor'       => auth()->user()->fullname,
     'actor_role'  => auth()->user()->access_level,
     'remarks'     => "FETS #{$fets->id} verified by DPSC",
 ]);

    // Spatie activity log - reuse property numbers already fetched
    activity()
        ->causedBy(auth()->user())
        ->performedOn($fets)
        ->withProperties([
            'fets_id'      => $fets->id,
            'property_no' => $logPropertyNos ?: 'N/A'
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

        // Get property numbers from junction table
        $propertyNumbers = DB::table('fets_items')
            ->where('fets_document_id', $fets->id)
            ->pluck('property_no')
            ->toArray();
        $userFullName = auth()->user()->fullname;
        $userAccessLevel = auth()->user()->access_level;

        // 🔹 Return-from-Repair Flow
        if (($fets->form_data['return_type'] ?? null) === 'from_repair') {

            // Inventory Update Logic: Update the returned units in the inventory table
            foreach ($propertyNumbers as $propNo) {
                $cleanedPropNo = preg_replace('/\s+/', '', $propNo);
                $originalReceiver = ($fets->form_data['original_receivers'] ?? [])[$cleanedPropNo] ?? DB::table('inventory')->whereRaw("REPLACE(TRIM(PROPERTY_NO),' ','') = ?", [$cleanedPropNo])->value('RECEIVER');
                $repairDestination = ($fets->form_data['repair_destinations'] ?? [])[$cleanedPropNo] ?? 'Unknown';

                DB::table('inventory')
                    ->whereRaw("REPLACE(TRIM(PROPERTY_NO),' ','') = ?", [$cleanedPropNo])
                    ->update([
                        'STATUS' => null, // Clear status as it is returned
                        'RECEIVER' => $originalReceiver, // Reassign to original receiver
                        'DPO_REMARKS' => "Returned from Repair: {$repairDestination}",
                        'updated_at' => now(),
                    ]);
            }

            // 💥 FIX: Conditionally close the original 'For Repair' FETS AND remove returned property numbers.
            // Get all original 'For Repair' FETS documents that contained any of the items just returned
            $originalFetsIds = DB::table('fets_items')
                ->whereIn('property_no', $propertyNumbers)
                ->pluck('fets_document_id')
                ->unique()
                ->toArray();
            
            $originalFetsToClose = FetsDocument::where('transfer_movement', 'For Repair')
                ->where('status', 'approved')
                ->whereIn('id', $originalFetsIds)
                ->get();

            // Check if the original FETS is fully completed before marking it as such
            foreach ($originalFetsToClose as $originalFets) {
                // Get ALL property numbers from the original FETS using junction table
                $originalPropNos = DB::table('fets_items')
                    ->where('fets_document_id', $originalFets->id)
                    ->pluck('property_no')
                    ->toArray();

                // Get ALL property numbers that have been successfully returned from repair
                $returnFetsIds = FetsDocument::where('transfer_movement', 'Return from Repair')
                    ->whereIn('status', ['approved', 'completed'])
                    ->pluck('id')
                    ->toArray();
                
                $successfulReturnPropNos = DB::table('fets_items')
                    ->whereIn('fets_document_id', $returnFetsIds)
                    ->whereIn('property_no', $originalPropNos) // Only check items from this original FETS
                    ->pluck('property_no')
                    ->unique()
                    ->toArray();

                // Items still considered 'out for repair' by the system
                $remainingForRepairPropNos = array_diff($originalPropNos, $successfulReturnPropNos);

                // Check if every item has been returned.
                $isFullyReturned = empty($remainingForRepairPropNos);

                if ($isFullyReturned) {
                    // FIX: Close the original FETS completely
                    $originalFets->status = 'completed';
                    $originalFets->save();
                } else {
                    // CRITICAL FIX: If not fully returned, update the FETS document to ONLY track the remaining items.
                    // This removes the lock for the items JUST RETURNED in this batch.
                    $originalFets->property_no = implode(',', $remainingForRepairPropNos);
                    $originalFets->save();
                }
            }

            // 2. Set the current return FETS to completed
            $fets->status = 'completed';
            $fets->save();

            // Logging the completion - get property numbers from junction table
            $completionLogPropertyNos = DB::table('fets_items')
                ->where('fets_document_id', $fets->id)
                ->pluck('property_no')
                ->join(',');
            
            FetsLog::create([
                'property_no' => $completionLogPropertyNos ?: 'N/A',
                'action' => 'completed',
                'actor' => $userFullName,
                'actor_role' => $userAccessLevel,
                'remarks' => "Return-from-Repair FETS #{$fets->id} completed/approved by {$userFullName}",
            ]);

            return back()->with('success', 'Return-from-Repair FETS approved and inventory updated.');
        }

        // 🔹 Normal FETS Approval Flow

        // Inventory Update Logic
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
            } elseif ($fets->transfer_movement === 'Return to Lender' && strtolower($fets->remarks) === 'unserviceable') {
                // Lock unserviceable items - mark them so they cannot be selected for FETS
                DB::table('inventory')->whereRaw("REPLACE(TRIM(PROPERTY_NO),' ','') = ?", [$cleanedPropNo])
                    ->update([
                        'STATUS' => 'Unserviceable',
                        'RECEIVER' => $receiverName,
                        'DPO_REMARKS' => 'Unserviceable - Returned to Lender',
                        'updated_at' => now()
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

        // Logging the approval - get property numbers from junction table
        $approveLogPropertyNos = DB::table('fets_items')
            ->where('fets_document_id', $fets->id)
            ->pluck('property_no')
            ->join(',');
        
        FetsLog::create([
            'property_no' => $approveLogPropertyNos ?: 'N/A',
            'action' => 'approved',
            'actor' => $userFullName,
            'actor_role' => $userAccessLevel,
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

    // Get property numbers from junction table
    $propertyNumbers = DB::table('fets_items')
        ->where('fets_document_id', $fets->id)
        ->pluck('property_no')
        ->toArray();

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

    // Log rejection - get property numbers from junction table
    $rejectLogPropertyNos = DB::table('fets_items')
        ->where('fets_document_id', $fets->id)
        ->pluck('property_no')
        ->join(',');
    
    FetsLog::create([
        'property_no' => $rejectLogPropertyNos ?: 'N/A',
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
    // For preview purposes, regenerate the PDF directly without creating a new FETS record

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
            // Get the property numbers from the junction table
            $propertyNos = DB::table('fets_items')
                ->where('fets_document_id', $doc->id)
                ->pluck('property_no')
                ->toArray();

            // Fallback to the property_no field if junction table is empty
            if (empty($propertyNos) && $doc->property_no) {
                $propertyNos = array_map('trim', explode(',', $doc->property_no));
            }

            if (empty($propertyNos)) {
                throw new \Exception('No property numbers found for this FETS document');
            }

            // Create a temporary request with edit_fets_id to update existing record
            $tempRequest = new Request([
                'selected' => $propertyNos,
                'transfer_movement' => $doc->transfer_movement,
                'remarks' => $doc->remarks,
                'repair_destination' => $doc->repair_destination,
                'to_receiver' => $doc->to_receiver,
                'to_office' => $doc->to_office,
                'edit_fets_id' => $doc->id,  // This tells generate() to update existing FETS
                'embed' => '1'
            ]);

            // Temporarily switch the request context
            $originalRequest = request();
            app()->instance('request', $tempRequest);

            try {
                // Call generate to regenerate PDF - it will update the existing FETS
                $result = $this->generate($tempRequest);
                
                // Refresh the document to get updated file paths
                $doc->refresh();

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
