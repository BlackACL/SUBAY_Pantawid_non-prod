<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FetsDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\FetsLog;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
use App\Models\Official;


class FetsController extends Controller
{

    // Provincial DPSC mapping
    private array $provincialDpsc = [
        'Davao Occidental' => 'Jeyson A. Alvarado',
        'Davao Del Sur'    => 'Ana Lou A. Albacite',
        'Davao City'       => 'Ivy Balbuena',
        'Davao Del Norte'  => 'Genevieve N. Jitotowani',
        'Davao De Oro'     => 'Gino Logronio',
        'Davao Oriental'   => 'Mayzel Dawn Rebuyon',
        'RPMO'             => 'Russell Allen S. Mozo',
    ];

    private string $headOfProperty = 'Al Jay Meliton';    

public function select(Request $request)
{
    $user = auth()->user();

    $receivers = DB::table('inventory')
        ->select('RECEIVER')
        ->distinct()
        ->pluck('RECEIVER');

    $allEquipment = DB::table('inventory')
        ->select('PROPERTY_NO', 'GENERAL_DESCRIPTION')
        ->get();

    $inProcessPropertyNos = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved']) // ✅ EXCLUDE rejected
        ->pluck('property_no')
        ->flatMap(function ($propertyNos) {
            return array_map('trim', explode(',', $propertyNos));
        })
        ->unique()
        ->toArray();

    $perPage = $request->input('per_page', session('per_page', 10));
    session(['per_page' => $perPage]);

    $inventory = DB::table('inventory');

    // Always show the current user's inventory
    $inventory->where('RECEIVER', $user->fullname);

    // Search by description, property no, or serial no
    if ($request->filled('search')) {
        $search = $request->search;

        $inventory->where(function ($query) use ($search) {
            $query->where('GENERAL_DESCRIPTION', 'like', '%' . $search . '%')
                  ->orWhere('PROPERTY_NO', 'like', '%' . $search . '%');
        });
    }

    $inventory = $inventory->orderBy('PROPERTY_NO')
        ->paginate($perPage)
        ->appends($request->except('page'));

    // ✅ Fetch repair destinations from DB
    $repairDestinations = \App\Models\RepairDestination::all();

    // ✅ Normalize province from user model (Option A)
    $normalizedProvince = strtolower(trim($user->province ?? ''));

    $provincialOfficial = \App\Models\Official::where('role', 'Provincial DPSC')
        ->whereRaw('LOWER(province) = ?', [$normalizedProvince])
        ->where('active', true)
        ->first();

    // ✅ Fetch Head of Property (active one only)
    $headOfProperty = \App\Models\Official::where('role', 'Head of Property')
        ->where('active', true)
        ->first();

    // ✅ Fallbacks if not assigned
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
        'repairDestinations',
        'provincialDisplay',
        'headOfPropertyDisplay'
    ));
}



public function selectEmbed(Request $request)
{
    $user = auth()->user();

    // Get receivers for dropdown
    $receivers = DB::table('inventory')->select('RECEIVER')->distinct()->pluck('RECEIVER');

    // All equipment for "Show All" option
    $allEquipment = DB::table('inventory')->select('PROPERTY_NO', 'GENERAL_DESCRIPTION')->get();

    // Prevent selecting items already in process
    $inProcessPropertyNos = FetsDocument::whereIn('status', ['submitted', 'verified', 'approved'])
        ->pluck('property_no')
        ->flatMap(fn($propertyNos) => array_map('trim', explode(',', $propertyNos)))
        ->unique()
        ->toArray();

    // Per page setting
    $perPage = $request->input('per_page', session('per_page', 10));
    session(['per_page' => $perPage]);

    // User inventory + search
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

    $inventory = $inventory->orderBy('PROPERTY_NO')
        ->paginate($perPage)
        ->appends($request->except('page'));

    return view('partials.FETS', [
        'receivers' => $receivers,
        'allEquipment' => $allEquipment,
        'inventory' => $inventory,
        'inProcessPropertyNos' => $inProcessPropertyNos,
        'hideNavbar' => true, // 👈 add this
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
    $validated = $request->validate([
        'property_no'        => 'nullable|string',
        'selected'           => 'required|array|min:1|max:5',
        'transfer_movement'  => 'required|string',
        'remarks'            => 'required|string',
        'repair_destination' => 'nullable|string|exists:repair_destinations,name',
        'to_receiver'        => 'nullable|string',
    ]);

    $user     = auth()->user();
    $remarks  = $validated['remarks'];
    $movement = $validated['transfer_movement'];

    // 🔹 Officials lookup
    $normalizedProvince = strtolower(trim($user->province ?? ''));
    $provincialDpsc = \App\Models\Official::where('role', 'Provincial DPSC')
        ->whereRaw('LOWER(province) = ?', [$normalizedProvince])
        ->where('active', true)
        ->first();
    $headOfProperty = \App\Models\Official::where('role', 'Head of Property')
        ->where('active', true)
        ->first();
    $regionalDpsc = \App\Models\Official::where('role', 'Regional DPSC')
        ->where('active', true)
        ->first();

    // 🔹 Recommending & Approving (DB-backed roles)
    $recommendingOfficial = \App\Models\Official::where('role', 'Recommending')
        ->where('active', true)
        ->first();
    $approvingOfficial = \App\Models\Official::where('role', 'Approving')
        ->where('active', true)
        ->first();

    $errors = [];
    if (!$provincialDpsc) {
        $errors[] = 'No Provincial DPSC assigned for your province. Contact Superadmin.';
    }
    if (!$headOfProperty) {
        $errors[] = 'No Head of Property assigned. Contact Superadmin.';
    }
    if (!$regionalDpsc) {
        $errors[] = 'No Regional DPSC assigned. Contact Superadmin.';
    }
    if (!$recommendingOfficial) {
        $errors[] = 'No Recommending official assigned. Contact Superadmin.';
    }
    if (!$approvingOfficial) {
        $errors[] = 'No Approving official assigned. Contact Superadmin.';
    }
    if (!empty($errors)) {
        return back()->with('error', implode(' | ', $errors));
    }

    // 🔹 Use dynamic names instead of hardcoded
    $recommendingName = $recommendingOfficial->fullname;
    $approvingName    = $approvingOfficial->fullname;

    // 🔹 Determine receiver (Provincial DPSC, Head of Property, Repair Destination, etc.)
    $toPerson = $this->determineReceiver($user, $movement, $remarks, $validated);

    $date        = now()->format('F m, Y');
    $fixedOffice = 'Pantawid (RPMO)';

    $propertyNo = $validated['property_no'] ?? null;
    if (!$propertyNo && !empty($validated['selected']) && count($validated['selected']) === 1) {
        $propertyNo = $validated['selected'][0];
    }

    // MULTI-UNIT PATH
    if (!empty($validated['selected']) && count($validated['selected']) > 1) {
        $allItems = DB::table('inventory')->whereIn('PROPERTY_NO', $validated['selected'])->get();

        $isLong = function ($items) use ($toPerson, $remarks) {
            return $items->contains(function ($item) use ($toPerson, $remarks) {
                return strlen($item->GENERAL_DESCRIPTION ?? '') > 120 ||
                       strlen($item->PROPERTY_NO ?? '') > 20 ||
                       strlen($item->SERIAL_NO ?? '') > 20 ||
                       strlen($item->PAR_NO ?? '') > 30 ||
                       strlen($item->RECEIVER ?? '') > 35 ||
                       strlen($toPerson) > 35 ||
                       strlen($remarks) > 25;
            });
        };

        $pdf        = new Fpdi();
        $configSet  = config('fets_coords');
        $chunkedPages = [];

        $startIndex = 0;
        $remaining  = count($allItems);

        while ($remaining > 0) {
            $fit = min($remaining, 5); // max template size is 5

            while ($fit > 0) {
                $useLong  = $isLong($allItems->slice($startIndex, $fit));
                $template = 'FETS-FO-9' . ($useLong ? '-long' : '') . '-for' . $fit . '.pdf';

                if (isset($configSet[$template])) {
                    $chunkedPages[] = [
                        'template' => $template,
                        'items'    => $allItems->slice($startIndex, $fit)->values(),
                        'config'   => $configSet[$template],
                    ];
                    $startIndex += $fit;
                    $remaining  -= $fit;
                    break;
                }

                $fit--;
            }

            if ($fit === 0) {
                return back()->with('error', 'No suitable template found for remaining items.');
            }
        }

        foreach ($chunkedPages as $page) {
            $templatePath = storage_path("app/templates/{$page['template']}");
            $pageCount    = $pdf->setSourceFile($templatePath);

            $isPage2Allowed = in_array($page['template'], [
                'FETS-FO-9-long.pdf',
                'FETS-FO-9-long-for2.pdf',
                'FETS-FO-9-long-for3.pdf',
                'FETS-FO-9-long-for4.pdf',
                'FETS-FO-9-long-for5.pdf',
            ]);

            $page2FieldsAlways      = ['requested_by', 'recommending', 'approving', 'received_by'];
            $page2ExtraForMultiLong = ['from_office', 'from_person', 'to_office', 'to_person'];

            $fieldsOnPage2 = $isPage2Allowed
                ? array_merge($page2FieldsAlways, in_array($page['template'], [
                    'FETS-FO-9-long-for3.pdf',
                    'FETS-FO-9-long-for4.pdf',
                    'FETS-FO-9-long-for5.pdf'
                ]) ? $page2ExtraForMultiLong : [])
                : [];

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl  = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                $cfg        = $page['config'];
                $items      = $page['items'];
                $lineHeight = $cfg['line_height'];

                $pdf->SetFont('Helvetica');
                $pdf->SetFontSize(8);
                $pdf->SetTextColor(0, 0, 0);

                if ($i === 1) {
                    foreach ($items as $idx => $item) {
                        $y = $cfg['base_y'] + ($cfg['y_offset'] * $idx);

                        $pdf->SetFont('Helvetica', '', 7);
                        $pdf->SetXY(15, $y);
                        $pdf->MultiCell(29, $lineHeight, $this->wrapCommaText($item->PROPERTY_NO), 0);

                        $pdf->SetXY(47, $y);
                        $pdf->MultiCell(30, $lineHeight, $this->wrapCommaText($item->SERIAL_NO ?? ''), 0);

                        $pdf->SetFont('Helvetica', '', 8);
                        $pdf->SetXY(79, $y);
                        $pdf->MultiCell(180, $lineHeight, $this->wrapDescription($item->GENERAL_DESCRIPTION ?? '', 80), 0);

                        $pdf->SetXY(205, $y);
                        $pdf->MultiCell(40, $lineHeight, $item->PAR_NO ?? '', 0);

                        $pdf->SetXY(247.5, $y);
                        $pdf->MultiCell(40, $lineHeight, ($movement === 'For Repair') ? 'Repair' : $remarks, 0);
                    }

                    $first = $items->first();
                    foreach ($cfg['fields'] ?? [] as $field => [$x, $y]) {
                        if (!in_array($field, $fieldsOnPage2)) {
                            $value = match ($field) {
                                'fets_no'       => '',
                                'fets_date'     => $date,
                                'from_office'   => $this->wrapPersonOffice($fixedOffice),
                                'to_office'     => $this->wrapPersonOffice($fixedOffice),
                                'from_person'   => $this->wrapPersonOffice($first->RECEIVER ?? ''),
                                'to_person'     => $this->wrapPersonOffice($toPerson),
                                'requested_by'  => $this->wrapPersonOffice($first->RECEIVER ?? ''),
                                'received_by'   => $this->wrapPersonOffice($toPerson),
                                'recommending'  => $this->wrapPersonOffice($recommendingName),
                                'approving'     => $this->wrapPersonOffice($approvingName),
                                default         => '',
                            };

                            $pdf->SetXY($x, $y);
                            $pdf->MultiCell(40, $lineHeight, $value, 0);
                        }
                    }
                }

                if ($i === 2) {
                    $first = $items->first();
                    foreach ($cfg['fields'] ?? [] as $field => [$x, $y]) {
                        if (in_array($field, $fieldsOnPage2)) {
                            $value = match ($field) {
                                'fets_no'       => '',
                                'fets_date'     => $date,
                                'from_office'   => $this->wrapPersonOffice($fixedOffice),
                                'to_office'     => $this->wrapPersonOffice($fixedOffice),
                                'from_person'   => $this->wrapPersonOffice($first->RECEIVER ?? ''),
                                'to_person'     => $this->wrapPersonOffice($toPerson),
                                'requested_by'  => $this->wrapPersonOffice($first->RECEIVER ?? ''),
                                'received_by'   => $this->wrapPersonOffice($toPerson),
                                'recommending'  => $this->wrapPersonOffice($recommendingName),
                                'approving'     => $this->wrapPersonOffice($approvingName),
                                default         => '',
                            };

                            $pdf->SetXY($x, $y);
                            $pdf->MultiCell(40, $lineHeight, $value, 0);
                        }
                    }
                }
            }
        }

        $fileName = 'fets_multi_' . now()->format('Ymd_His') . '.pdf';
        $filePath = "public/fets/{$fileName}";
        Storage::put($filePath, $pdf->Output('S'));

        $fets = FetsDocument::create([
            'property_no'       => implode(',', $validated['selected']),
            'to_receiver'       => $toPerson,
            'remarks'           => ($movement === 'For Repair') ? 'Repair' : $remarks,
            'transfer_movement' => $movement,
            'repair_destination'=> ($movement === 'For Repair') ? $validated['repair_destination'] : null,
            'user_id'           => $user->id,
            'file_name'         => $fileName,
            'file_path'         => $filePath,
            'status'            => 'submitted',
        ]);

        return redirect()->route('fets.select')->with([
            'success' => 'FETS submitted and PDF generated.',
            'fets_id' => $fets->id,
            'fets_preview_url' => route('fets.preview', ['id' => $fets->id]),
            'fets_download_url' => route('fets.download', ['id' => $fets->id]),
        ]);
    }

    // SINGLE UNIT
    if (!$propertyNo) {
        return back()->with('error', 'Please select at least one equipment.');
    }

    $item = DB::table('inventory')->where('PROPERTY_NO', $propertyNo)->first();
    if (!$item) {
        return back()->with('error', 'Equipment not found.');
    }

    $useLong = (
        strlen($item->GENERAL_DESCRIPTION ?? '') > 120 ||
        strlen($item->SERIAL_NO ?? '') > 50 ||
        strlen($item->RECEIVER ?? '') > 35 ||
        strlen($toPerson) > 35
    );

    $template     = $useLong ? 'FETS-FO-9-long.pdf' : 'FETS-FO-9.pdf';
    $templatePath = storage_path("app/templates/{$template}");

    $configSet = config('fets_coords');
    if (!isset($configSet[$template])) {
        return back()->with('error', "Template coordinates not found for '{$template}'.");
    }

    $cfg        = $configSet[$template];
    $fields     = $cfg['fields'];
    $lineHeight = $cfg['line_height'];

    $pdf       = new Fpdi();
    $pageCount = $pdf->setSourceFile($templatePath);

    for ($i = 1; $i <= $pageCount; $i++) {
        $tpl  = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);

        if ($i === 1) {
            $pdf->SetFont('Helvetica');
            $pdf->SetFontSize(8);
            $pdf->SetTextColor(0, 0, 0);

            foreach ($fields as $field => [$x, $y]) {
                $value = match ($field) {
                    'fets_no'       => '',
                    'fets_date'     => $date,
                    'property_no'   => $this->wrapCommaText($item->PROPERTY_NO, 20),
                    'serial_no'     => $this->wrapCommaText($item->SERIAL_NO ?? ''),
                    'description'   => $this->wrapDescription($item->GENERAL_DESCRIPTION ?? '', 80),
                    'par_no'        => $item->PAR_NO ?? '',
                    'remarks'       => ($movement === 'For Repair') ? 'Repair' : $remarks,
                    'from_office'   => $this->wrapPersonOffice($fixedOffice),
                    'to_office'     => $this->wrapPersonOffice($fixedOffice),
                    'from_person'   => $this->wrapPersonOffice($item->RECEIVER ?? ''),
                    'to_person'     => $this->wrapPersonOffice($toPerson),
                    'requested_by'  => $this->wrapPersonOffice($item->RECEIVER ?? ''),
                    'received_by'   => $this->wrapPersonOffice($toPerson),
                    'recommending'  => $this->wrapPersonOffice($recommendingName),
                    'approving'     => $this->wrapPersonOffice($approvingName),
                    default         => '',
                };

                $pdf->SetXY($x, $y);
                $pdf->MultiCell(40, $lineHeight, $value, 0);
            }
        }
    }

    $fileName = 'fets_' . now()->format('Ymd_His') . '.pdf';
    $filePath = "public/fets/{$fileName}";
    Storage::put($filePath, $pdf->Output('S'));

    $fets = FetsDocument::create([
        'property_no'       => $item->PROPERTY_NO,
        'to_receiver'       => $toPerson,
        'remarks'           => ($movement === 'For Repair') ? 'Repair' : $remarks,
        'transfer_movement' => $movement,
        'repair_destination'=> ($movement === 'For Repair') ? $validated['repair_destination'] : null,
        'user_id'           => $user->id,
        'file_name'         => $fileName,
        'file_path'         => $filePath,
        'status'            => 'submitted',
    ]);

    $redirectRoute = $request->has('embed') ? 'fets.select.embed' : 'fets.select';

    return redirect()
        ->route($redirectRoute)
        ->with([
            'success' => 'FETS submitted and PDF generated.',
            'fets_id' => $fets->id,
            'fets_preview_url' => route('fets.preview', ['id' => $fets->id]),
            'fets_download_url' => route('fets.download', ['id' => $fets->id]),
            'hideNavbar' => true,
        ]);
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



private function determineReceiver($user, string $movement, string $remarks, array $validated): string
{
    // Normalize user province
    $province = strtolower(trim($user->province ?? ''));

    // Handle different movements
    switch ($movement) {

        case 'Return to Lender':
        case 'For Surrender':
            // Serviceable → Provincial DPSC
            if (strtolower($remarks) === 'serviceable') {
                $provincialDpsc = \App\Models\Official::where('role', 'Provincial DPSC')
                    ->whereRaw('LOWER(province) = ?', [$province])
                    ->where('active', true)
                    ->first();

                if ($provincialDpsc) {
                    return $provincialDpsc->fullname;
                }

                return "[ERROR: No active Provincial DPSC assigned for {$user->province}]";
            }

            // Unserviceable → Head of Property
            $headOfProperty = \App\Models\Official::where('role', 'Head of Property')
                ->where('active', true)
                ->first();

            if ($headOfProperty) {
                return $headOfProperty->fullname;
            }

            return "[ERROR: No active Head of Property found]";

        case 'For Repair':
            // Must provide repair_destination via form (DB-backed dropdown)
            if (!empty($validated['repair_destination'])) {
                return $validated['repair_destination'];
            }
            return "[ERROR: Repair destination missing]";

        case 'For Reissuance':
            // Only DPSC users can reissue
            if (in_array($user->access_level, ['Provincial DPSC', 'Regional DPSC'])) {
                return $validated['to_receiver'] ?? '[ERROR: Receiver not specified for reissuance]';
            }

            throw new \Exception('Unauthorized transfer movement for non-DPSC users.');

        default:
            // Fallback to manually provided receiver (if any)
            return $validated['to_receiver'] ?? '[ERROR: Receiver not specified]';
    }
}





 public function reviewSubmitted()
{   
    $documents = FetsDocument::where('status', 'submitted')
        ->orderByDesc('created_at')
        ->paginate(10);

    return view('adminDPSC.Provincial.FETSrequest', compact('documents'));
}

    // ✅ View for Provincial to see already VERIFIED FETS
public function showVerified(Request $request)
{
    $query = FetsDocument::where('status', 'verified');

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



public function showVerifiedRegional()
{
    $documents = FetsDocument::where('status', 'verified')
                    ->orderByDesc('created_at')
                    ->paginate(10);

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

    // Ensure status is verified before approval
    if ($fets->status !== 'verified') {
        return back()->with('error', 'Only verified FETS can be approved.');
    }

    // Update FETS document to approved
    $fets->status = 'approved';
    $fets->approved_by = auth()->id();
    $fets->save();

    // Handle multiple property numbers (comma-separated)
    $propertyNumbers = array_map('trim', explode(',', $fets->property_no));

    // 🔹 Determine the correct DPSC official as receiver
    $dpscOfficial = null;

    if ($fets->transfer_movement === 'Regional Approval') {
        // For regional approval, find active Regional DPSC for the employee's region
        $dpscOfficial = Official::where('role', 'Regional DPSC')
            ->where('province', $fets->region)
            ->where('active', true)
            ->first();
    } else {
        // For provincial approval, find active Provincial DPSC for the employee's province
        $dpscOfficial = Official::where('role', 'Provincial DPSC')
            ->where('province', $fets->province)
            ->where('active', true)
            ->first();
    }

    // Use the official's fullname as the receiver
    $receiverName = $dpscOfficial ? $dpscOfficial->fullname : $fets->to_receiver;

    foreach ($propertyNumbers as $propNo) {
        $cleanedPropNo = preg_replace('/\s+/', '', $propNo);

        if ($fets->transfer_movement === 'For Repair') {
            // Lock device and set status to Being Assessed for Repair
            DB::table('inventory')
                ->whereRaw("REPLACE(TRIM(PROPERTY_NO), ' ', '') = ?", [$cleanedPropNo])
                ->update([
                    'STATUS' => 'Being Assessed for Repair',
                    'DPO_REMARKS' => DB::raw("CONCAT(IFNULL(DPO_REMARKS, ''), ' | Assigned for Repair to: {$fets->repair_destination}')"),
                    'updated_at' => now(),
                ]);
        } else {
            // Regular approval: update receiver to DPSC official
            DB::table('inventory')
                ->whereRaw("REPLACE(TRIM(PROPERTY_NO), ' ', '') = ?", [$cleanedPropNo])
                ->update([
                    'RECEIVER' => $receiverName,
                    'DPO_REMARKS' => DB::raw("CONCAT(IFNULL(DPO_REMARKS, ''), ' | Approved via FETS ID {$fets->id}')"),
                    'updated_at' => now(),
                ]);
        }
    }

    // Log this approval
    FetsLog::create([
        'property_no' => $fets->property_no,
        'action'      => 'approved',
        'actor'       => auth()->user()->fullname,
        'actor_role'  => auth()->user()->access_level,
        'remarks'     => ($fets->transfer_movement === 'For Repair')
            ? "FETS #{$fets->id} approved for repair assessment to {$fets->repair_destination}"
            : "FETS #{$fets->id} approved by Regional DPSC, assigned to {$receiverName}",
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

    // Log the action
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

    $allowed = $user->id === $doc->user_id || in_array($user->access_level, ['superadmin', 'Provincial DPSC', 'Regional DPSC']);

    if (!$allowed) {
        abort(403, 'Unauthorized access');
    }

    $filePath = storage_path("app/{$doc->file_path}");

    if (!file_exists($filePath)) {
        abort(404, 'File not found');
    }

    return response()->file($filePath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $doc->file_name . '"'
    ]);
}


}