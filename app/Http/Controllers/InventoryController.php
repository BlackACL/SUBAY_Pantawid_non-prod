<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;


class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'twofactor']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = DB::table('inventory');

        // Determine if we should filter by receiver
        $showAll = $request->has('show_all') && $request->get('show_all') == '1';

        if (!$showAll && !in_array($user->access_level, ['Regional DPSC', 'Provincial DPSC'])) {
            $query->where('RECEIVER', $user->fullname);
        }

        // Optional filters via dropdowns
        if ($request->filled('receiver')) {
            $query->where('RECEIVER', $request->receiver);
        }

        if ($request->filled('office')) {
            $query->where('OFFICE', $request->office);
        }

        $inventory = $query->paginate(15)->appends($request->query());

        $receivers = DB::table('inventory')->select('RECEIVER')->distinct()->whereNotNull('RECEIVER')->pluck('RECEIVER');
        $offices = DB::table('inventory')->select('OFFICE')->distinct()->whereNotNull('OFFICE')->pluck('OFFICE');

        return view('inventory.upload', compact('inventory', 'receivers', 'offices', 'showAll'));
    }



    public function showMyInventory(Request $request)
    {
        $user = auth()->user();
        $role = $user->access_level;
        $fullname = $user->fullname;

        // Log who is accessing the page
        Log::info("MyInventory accessed by: {$fullname} ({$role})");

        // Checkbox flag
        $showAll = $request->input('show_all') === '1';
        Log::info("Show all flag: " . ($showAll ? 'YES' : 'NO'));

        $query = DB::table('inventory');

        // By default, filter by fullname
        if (!($showAll && in_array($role, ['Regional DPSC', 'Provincial DPSC']))) {
            $query->where('RECEIVER', $fullname);
            Log::info("Filtering inventory by RECEIVER = {$fullname}");
        } else {
            Log::info("Showing ALL inventory for role: {$role}");
        }

        // Optional filters
        if ($request->filled('receiver')) {
            $query->where('RECEIVER', 'like', '%' . $request->receiver . '%');
            Log::info("Applied receiver filter: " . $request->receiver);
        }

        if ($request->filled('office')) {
            $query->where('OFFICE', 'like', '%' . $request->office . '%');
            Log::info("Applied office filter: " . $request->office);
        }

        $inventory = $query->orderBy('PROPERTY_NO')
            ->paginate(15)
            ->appends($request->all());

        // Log number of results found
        Log::info("Inventory items retrieved: " . $inventory->total());

        $receivers = DB::table('inventory')
            ->select('RECEIVER')
            ->distinct()
            ->pluck('RECEIVER');

        $offices = DB::table('inventory')
            ->select('OFFICE')
            ->distinct()
            ->pluck('OFFICE');

        return view(
            'adminDPSC.' . ($role === 'Regional DPSC' ? 'Regional' : 'Provincial') . '.MyInventory',
            compact('inventory', 'receivers', 'offices', 'showAll')
        );
    }


    public function showEmployeeInventory(Request $request)
    {
        $fullname = auth()->user()->fullname;

        // Debug: Log the user's fullname
        Log::info("User fullname: {$fullname}");

        // Try exact match first, then case-insensitive match
        $query = DB::table('inventory')->where(function ($q) use ($fullname) {
            $q->where('RECEIVER', $fullname)
                ->orWhere('RECEIVER', 'like', '%' . $fullname . '%')
                ->orWhere('RECEIVER', 'like', '%' . strtoupper($fullname) . '%')
                ->orWhere('RECEIVER', 'like', '%' . strtolower($fullname) . '%');
        });

        if ($request->filled('receiver')) {
            $query->where('RECEIVER', 'like', '%' . $request->receiver . '%');
        }

        if ($request->filled('office')) {
            $query->where('OFFICE', 'like', '%' . $request->office . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                ->orWhere('SERIAL_NO', 'like', "%{$search}%")
                ->orWhere('PROPERTY_NO', 'like', "%{$search}%");
            });
        }

        

        $inventory = $query->orderBy('PROPERTY_NO')->paginate(15)->appends($request->all());

        $receivers = DB::table('inventory')->select('RECEIVER')->distinct()->whereNotNull('RECEIVER')->pluck('RECEIVER');
        $offices = DB::table('inventory')->select('OFFICE')->distinct()->whereNotNull('OFFICE')->pluck('OFFICE');

        return view('inventory', compact('inventory', 'receivers', 'offices'));
    }

    public function select(Request $request)
    {
        $user = auth()->user();

        $query = DB::table('inventory')
            ->where('RECEIVER', $user->fullname); // Default: user’s own inventory

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                ->orWhere('SERIAL_NO', 'like', "%{$search}%")
                ->orWhere('PROPERTY_NO', 'like', "%{$search}%");
            });
        }

        // Per page
        $perPage = $request->get('per_page', session('per_page', 10));
        session(['per_page' => $perPage]);

        // Paginate filtered results
        $inventory = $query->orderBy('PROPERTY_NO')
            ->paginate($perPage)
            ->appends($request->query());

        $receivers = DB::table('inventory')->select('RECEIVER')->distinct()->pluck('RECEIVER');
        $allEquipment = DB::table('inventory')->get();
        $inProcessPropertyNos = DB::table('fets_requests')
            ->where('status', 'in process')
            ->pluck('property_no')
            ->toArray();

        return view('FETS', compact(
            'inventory',
            'receivers',
            'allEquipment',
            'inProcessPropertyNos'
        ));
    }


    public function showUploadForm()
    {
        $user = Auth::user();

        if (!in_array($user->access_level, ['Regional DPSC', 'Provincial DPSC'])) {
            abort(403, 'Unauthorized.');
        }

        return view('inventory.upload');
    }

    public function upload(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->access_level, ['Regional DPSC', 'Provincial DPSC'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:10240',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to open file.');
        }

        $originalHeader = fgetcsv($handle);
        if (isset($originalHeader[0])) {
            $originalHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $originalHeader[0]); // Strip UTF-8 BOM
        }

        $normalizedHeader = array_map(function ($col) {
            $col = trim($col);
            $col = preg_replace('/[^A-Z0-9]/i', '_', $col);
            $col = preg_replace('/_+/', '_', $col);
            return trim(strtoupper($col), '_');
        }, $originalHeader);

        DB::table('inventory')->truncate();

        $insertData = [];
        $rowCount = 0;
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map(fn($v) => mb_convert_encoding($v, 'UTF-8', mb_detect_encoding($v, 'UTF-8, ISO-8859-1, ISO-8859-15', true)), $row);
            $data = array_combine($normalizedHeader, $row);
            $data = array_map(fn($v) => trim(preg_replace('/\s+/', ' ', $v)), $data); // Normalize and trim all values
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $insertData[] = $data;

            // Debug: Log some sample data
            if ($rowCount < 5) {
                Log::info("Sample row {$rowCount}: " . json_encode($data));
            }

            if (count($insertData) >= 1000) {
                DB::table('inventory')->insert($insertData);
                $insertData = [];
            }
            $rowCount++;
        }

        fclose($handle);
        if (!empty($insertData)) {
            DB::table('inventory')->insert($insertData);
        }

        // Store uploaded file
        $file = $request->file('csv_file');
        $file->storeAs('public/inventory_uploads', 'inventory_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension());

        return redirect()->route('inventory.index')->with('success', "Inventory uploaded successfully! {$rowCount} rows processed.");
    }

    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="inventory_export.csv"',
        ];

        // Headers exactly matching your RPMO_Inventory.csv
        $csvHeaders = [
            'FUND CODE',
            'PROPERTY STATUS',
            'ARTICLE DESCRIPTION',
            'GENERAL DESCRIPTION',
            'SERIAL NO.',
            'PROPERTY NO',
            'PAR NO',
            'PAR DATE',
            'UNIT',
            'QTY',
            'ACQUISITION COST',
            'ACQUISITION DATE',
            'RECEIVER',
            'SUBPAR',
            'ACCOUNT CODE',
            'WARRANTY',
            'OFFICE',
            'FOUND IN STATION?',
            'LABELLED?',
            'DPO REMARKS'
        ];

        // Corresponding DB columns
        $dbColumns = [
            'FUND_CODE',
            'PROPERTY_STATUS',
            'ARTICLE_DESCRIPTION',
            'GENERAL_DESCRIPTION',
            'SERIAL_NO',
            'PROPERTY_NO',
            'PAR_NO',
            'PAR_DATE',
            'UNIT',
            'QTY',
            'ACQUISITION_COST',
            'ACQUISITION_DATE',
            'RECEIVER',
            'SUBPAR',
            'ACCOUNT_CODE',
            'WARRANTY',
            'OFFICE',
            'FOUND_IN_STATION',
            'LABELLED',
            'DPO_REMARKS'
        ];

        $callback = function () use ($csvHeaders, $dbColumns) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, $csvHeaders);

            DB::table('inventory')->orderBy('PROPERTY_NO')->chunk(1000, function ($rows) use ($handle, $dbColumns) {
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($dbColumns as $col) {
                        $line[] = $row->$col ?? '';
                    }
                    fputcsv($handle, $line);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
