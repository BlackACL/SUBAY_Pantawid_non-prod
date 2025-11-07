<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'twofactor']);
    }

    /* ===========================
       INDEX & SHOW METHODS
       =========================== */

    public function superadminInventory(Request $request)
    {
        // Superadmin can see ALL inventory from all users
        $query = DB::table('inventory')
            ->whereNotNull('PROPERTY_NO')
            ->where('PROPERTY_NO', '!=', '');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('RECEIVER', 'like', "%{$search}%")
                  ->orWhere('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                  ->orWhere('SERIAL_NO', 'like', "%{$search}%")
                  ->orWhere('PROPERTY_NO', 'like', "%{$search}%");
            });
        }

        $inventory = $query->orderBy('PROPERTY_NO')->paginate(10)->appends($request->all());

        return view('superadmin.inventory.index', compact('inventory'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = DB::table('inventory');

        $showAll = $request->has('show_all') ? $request->get('show_all') == '1' : false;

        // Apply access level restrictions
        if (!$showAll && !in_array($user->access_level, ['Superadmin', 'Regional DPSC', 'Provincial DPSC'])) {
            // Regular users can only see their own inventory
            $query->where('RECEIVER', $user->fullname);
        } elseif ($showAll) {
            // Apply province-based filtering for Provincial DPSC users
            if ($user->access_level === 'Provincial DPSC' && !empty($user->province)) {
                // Get all users from the same province
                $provinceUsers = User::where('province', $user->province)
                    ->whereNotNull('fullname')
                    ->pluck('fullname')
                    ->toArray();

                if (!empty($provinceUsers)) {
                    $query->whereIn('RECEIVER', $provinceUsers);
                }
            }
            // Superadmin and Regional DPSC can see all inventories (no additional filtering)
        }

        if ($request->filled('receiver')) $query->where('RECEIVER', $request->receiver);
        if ($request->filled('office')) $query->where('OFFICE', $request->office);

        $inventory = $query->orderBy('PROPERTY_NO')->paginate(15)->appends($request->query());

        // Get distinct values for filters (also apply province filtering for Provincial DPSC)
        $receiversQuery = Inventory::select('RECEIVER')->distinct()->whereNotNull('RECEIVER');
        $officesQuery = Inventory::select('OFFICE')->distinct()->whereNotNull('OFFICE');

        // Apply province filtering to filter options for Provincial DPSC
        if ($user->access_level === 'Provincial DPSC' && !empty($user->province)) {
            $provinceUsers = User::where('province', $user->province)
                ->whereNotNull('fullname')
                ->pluck('fullname')
                ->toArray();

            if (!empty($provinceUsers)) {
                $receiversQuery->whereIn('RECEIVER', $provinceUsers);
                $officesQuery->whereIn('RECEIVER', $provinceUsers);
            }
        }

        $receivers = $receiversQuery->pluck('RECEIVER')->toArray();
        $offices = $officesQuery->pluck('OFFICE')->toArray();
        $files = Inventory::select('source_file')->distinct()->whereNotNull('source_file')->pluck('source_file')->toArray();

        return view('inventory.upload', compact('inventory', 'receivers', 'offices', 'files', 'showAll'));
    }


    public function showEmployeeInventory(Request $request)
    {
    $fullname = auth()->user()->fullname;

    $query = DB::table('inventory')->where(function ($q) use ($fullname) {
        $q->where('RECEIVER', $fullname)
            ->orWhere('RECEIVER', 'like', '%' . $fullname . '%')
            ->orWhere('RECEIVER', 'like', '%' . strtoupper($fullname) . '%')
            ->orWhere('RECEIVER', 'like', '%' . strtolower($fullname) . '%');
    });

    // apply search to Description, Serial No, or Property No
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('GENERAL_DESCRIPTION', 'like', '%' . $search . '%')
              ->orWhere('SERIAL_NO', 'like', '%' . $search . '%')
              ->orWhere('PROPERTY_NO', 'like', '%' . $search . '%');
        });
    }

    if ($request->filled('receiver')) {
        $query->where('RECEIVER', 'like', '%' . $request->receiver . '%');
    }

    if ($request->filled('office')) {
        $query->where('OFFICE', 'like', '%' . $request->office . '%');
    }

    $inventory = $query->orderBy('PROPERTY_NO')
        ->paginate(15)
        ->appends($request->all());

    $receivers = DB::table('inventory')->select('RECEIVER')->distinct()->whereNotNull('RECEIVER')->pluck('RECEIVER');
    $offices = DB::table('inventory')->select('OFFICE')->distinct()->whereNotNull('OFFICE')->pluck('OFFICE');

    return view('inventory', compact('inventory', 'receivers', 'offices'));
}



    public function showMyInventory(Request $request)
    {
        $user = auth()->user();
        $role = $user->access_level;
        $fullname = $user->fullname;
        $showAll = $request->input('show_all') === '1';

        $query = DB::table('inventory')
            ->whereNotNull('PROPERTY_NO')
            ->where('PROPERTY_NO', '!=', '');

        if (!(($role === 'Regional DPSC' || $role === 'Provincial DPSC') && $showAll)) {
            $query->where('RECEIVER', $fullname);
        } elseif ($role === 'Provincial DPSC' && $showAll && !empty($user->province)) {
            // Apply province filtering for Provincial DPSC users when showing all equipment
            // Exclude the current user's own inventory - only show other employees' inventory
            $provinceUsers = User::where('province', $user->province)
                ->where('fullname', '!=', $fullname)  // Exclude current user
                ->whereNotNull('fullname')
                ->pluck('fullname')
                ->toArray();

            if (!empty($provinceUsers)) {
                $query->whereIn('RECEIVER', $provinceUsers);
            } else {
                // If no other users in province, show empty result
                $query->where('RECEIVER', 'NEVER_MATCH_THIS_NAME');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('RECEIVER', 'like', "%{$search}%")
                  ->orWhere('GENERAL_DESCRIPTION', 'like', "%{$search}%")
                  ->orWhere('SERIAL_NO', 'like', "%{$search}%")
                  ->orWhere('PROPERTY_NO', 'like', "%{$search}%");
            });
        }

        $inventory = $query->orderBy('PROPERTY_NO')->paginate(10)->appends($request->all());

        $receivers = DB::table('inventory')->select('RECEIVER')->distinct()->pluck('RECEIVER');
        $offices = DB::table('inventory')->select('OFFICE')->distinct()->pluck('OFFICE');

        return view('adminDPSC.' . ($role === 'Regional DPSC' ? 'Regional' : 'Provincial') . '.MyInventory', compact('inventory', 'receivers', 'offices', 'showAll'));
    }

    /* ===========================
       UPLOAD / IMPORT
       =========================== */

public function showUploadForm()
{
    $user = Auth::user();
    if (!in_array($user->access_level, ['Superadmin', 'Regional DPSC', 'Provincial DPSC'])) {
        abort(403, 'Unauthorized.');
    }

    $showAll = false;

    // Get distinct source files for export dropdown
    $files = Inventory::select('source_file')
                ->distinct()
                ->whereNotNull('source_file')
                ->pluck('source_file')
                ->toArray();

    return view('inventory.upload', compact('showAll', 'files'));
}



public function upload(Request $request)
{
    $user = Auth::user();
    if (!in_array($user->access_level, ['Superadmin', 'Regional DPSC', 'Provincial DPSC'])) {
        abort(403, 'Unauthorized.');
    }

    $request->validate([
        'files.*' => 'required|file|mimes:csv,txt,xlsx|max:20480',
    ]);

    $files = $request->file('files');
    if (!$files || count($files) === 0) {
        return response()->json(['message' => 'No files uploaded.'], 422);
    }

    $overallReport = [];
    foreach ($files as $file) {
        $filename = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            DB::beginTransaction();

            $rows = match ($extension) {
                'csv', 'txt' => $this->parseCsvOrTxt($file),
                'xlsx' => $this->parseXlsx($file),
                default => throw new \Exception("Unsupported file type: {$extension}"),
            };

            // Helper function for robust name normalization
            $normalizeName = function($name) {
                if (is_null($name)) return null;
                $no_punctuation = preg_replace('/[\s,]+/u', '', $name);
                return mb_strtolower($no_punctuation, 'UTF-8');
            };

            // Get a map of normalized_name => original_name for validation
            $usersQuery = User::whereNotNull('fullname');
            if ($user->access_level === 'Provincial DPSC' && !empty($user->province)) {
                $usersQuery->where('province', $user->province);
            }
            $existingUserMap = $usersQuery->get()->pluck('fullname', 'fullname')->mapWithKeys(function($originalName) use ($normalizeName) {
                return [$normalizeName($originalName) => $originalName];
            });

            // ✅ RE-ADDED: Initialize arrays for detailed reporting
            $processedRows = 0;
            $failedRows = 0;
            $updatedRows = 0;
            $validationErrors = [];
            $insertedData = [];
            $updatedData = [];
            $failedData = [];
            $now = now();

            foreach ($rows as $rowIndex => $row) {
                $row = $this->normalizeRow($row);

                // Prepare row data for detailed reporting
                $rowData = [
                    'no' => $rowIndex + 2,
                    'general_description' => $row['GENERAL_DESCRIPTION'] ?? 'N/A',
                    'serial_no' => $row['SERIAL_NO'] ?? 'N/A',
                    'property_no' => $row['PROPERTY_NO'] ?? 'N/A',
                    'receiver' => $row['RECEIVER'] ?? 'N/A',
                ];

                // Check for required fields
                if (empty($row['PROPERTY_NO']) || empty($row['RECEIVER'])) {
                    $failedRows++;
                    $rowData['error'] = 'Missing required fields (PROPERTY_NO or RECEIVER)';
                    $failedData[] = $rowData;
                    // ✅ RE-ADDED: Detailed validation error
                    $validationErrors[] = ['row' => $rowIndex + 2, 'error' => 'Missing required fields'];
                    continue;
                }

                // Check if RECEIVER exists using normalized names
                $receiverNameFromFile = trim($row['RECEIVER']);
                $normalizedReceiver = $normalizeName($receiverNameFromFile);

                if (!isset($existingUserMap[$normalizedReceiver])) {
                    $failedRows++;
                    $errorMessage = "Receiver '{$receiverNameFromFile}' is not an existing user in the system.";
                    if ($user->access_level === 'Provincial DPSC') {
                        $errorMessage = "Receiver '{$receiverNameFromFile}' is not an existing user in your province ({$user->province}).";
                    }
                    $rowData['error'] = $errorMessage;
                    $failedData[] = $rowData;
                    // ✅ RE-ADDED: Detailed validation error
                    $validationErrors[] = ['row' => $rowIndex + 2, 'error' => $errorMessage];
                    continue;
                }

                // Use the original, correctly formatted name from the database for consistency
                $row['RECEIVER'] = $existingUserMap[$normalizedReceiver];
                $row['source_file'] = $filename;
                $row['imported_at'] = $now;
                $row = array_intersect_key($row, array_flip((new Inventory)->getFillable()));

                // The main bug fix: use only PROPERTY_NO to find records
                $inventory = Inventory::updateOrCreate(
                    ['PROPERTY_NO' => $row['PROPERTY_NO']],
                    $row
                );

                // ✅ RE-ADDED: Populate detailed success lists
                if ($inventory->wasRecentlyCreated) {
                    $processedRows++;
                    $insertedData[] = $rowData;
                } else {
                    $updatedRows++;
                    $updatedData[] = $rowData;
                }
            }

            DB::commit();

            // ✅ RE-ADDED: Include detailed lists in the final report
            $overallReport[] = [
                'file' => $filename,
                'processed' => $processedRows,
                'updated' => $updatedRows,
                'failed' => $failedRows,
                'status' => 'success',
                'validation_errors' => $validationErrors,
                'inserted_data' => $insertedData,
                'updated_data' => $updatedData,
                'failed_data' => $failedData
            ];

            $file->storeAs('public/inventory_uploads', 'inventory_' . now()->format('Ymd_His_') . Str::slug(pathinfo($filename, PATHINFO_FILENAME)) . '.' . $extension);

        } catch (\Exception $e) {
            DB::rollBack();
            $overallReport[] = ['file' => $filename, 'status' => 'failed', 'error' => $e->getMessage()];
            Log::error("Inventory import failed for $filename: " . $e->getMessage());
        }
    }

    if ($request->ajax()) {
        return response()->json(['message' => 'Files processed.', 'report' => $overallReport]);
    }

    return redirect()->route('inventory.index')->with('success', 'Files processed.')->with('report', $overallReport);
}

protected function parseCsvOrTxt($file)
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) throw new \Exception('Unable to open file.');

        $header = fgetcsv($handle);
        if (isset($header[0])) $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (empty(array_filter($data, fn($v) => !is_null($v) && $v !== ''))) continue;
            $row = [];
            foreach ($header as $i => $col) {
                $value = $data[$i] ?? null;
                if (is_string($value)) $value = trim(preg_replace('/\s+/', ' ', $value));
                $row[$col] = $value;
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    protected function normalizeRow($row)
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $key = trim($key);
            $key = preg_replace('/[^A-Z0-9]/i', '_', $key);
            $key = preg_replace('/_+/', '_', $key);
            $key = trim(strtoupper($key), '_');
            $normalized[$key] = is_string($value) ? trim(preg_replace('/\s+/', ' ', $value)) : $value;
        }
        return $normalized;
    }

    /* ===========================
       EXPORT
       =========================== */

public function export(Request $request)
{
    $file = $request->query('file'); // optional

    $report = [
        'file' => $file ?? 'FULL_DB',
        'exported_rows' => 0,
        'timestamp' => now()->format('Y-m-d H:i:s'),
    ];

    $callback = function () use ($file, &$report) {
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        $csvHeaders = [
            'FUND CODE','PROPERTY STATUS','ARTICLE DESCRIPTION','GENERAL DESCRIPTION','SERIAL NO.',
            'PROPERTY NO','PAR NO','PAR DATE','UNIT','QTY','ACQUISITION COST','ACQUISITION DATE',
            'RECEIVER','SUBPAR','ACCOUNT CODE','WARRANTY','OFFICE','FOUND IN STATION?','LABELLED?','DPO REMARKS'
        ];
        fputcsv($handle, $csvHeaders);

        $dbColumns = [
            'FUND_CODE','PROPERTY_STATUS','ARTICLE_DESCRIPTION','GENERAL_DESCRIPTION','SERIAL_NO',
            'PROPERTY_NO','PAR_NO','PAR_DATE','UNIT','QTY','ACQUISITION_COST','ACQUISITION_DATE',
            'RECEIVER','SUBPAR','ACCOUNT_CODE','WARRANTY','OFFICE','FOUND_IN_STATION','LABELLED','DPO_REMARKS'
        ];

        $query = Inventory::query();
        if ($file) $query->where('source_file', $file);

        $query->orderBy('PROPERTY_NO')->chunk(1000, function ($rows) use ($handle, $dbColumns, &$report) {
            foreach ($rows as $row) {
                $line = [];
                foreach ($dbColumns as $col) $line[] = $row->$col ?? '';
                fputcsv($handle, $line);
                $report['exported_rows']++;
            }
        });

        fclose($handle);
    };

    // AJAX request: return report only
    if ($request->ajax()) {
        return response()->json([
            'message' => 'Export completed.',
            'report' => $report,
        ]);
    }

    // Normal CSV download
    $filename = 'inventory_export';
    if ($file) $filename .= '_' . pathinfo($file, PATHINFO_FILENAME);
    $filename .= '_' . now()->format('Ymd_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    return response()->stream($callback, 200, $headers);
}





    /* ===========================
       CLEAR INVENTORY
       =========================== */

    public function clearInventory(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->access_level, ['Superadmin', 'Regional DPSC'])) {
            abort(403, 'Unauthorized.');
        }

        if (!$request->input('confirm') || $request->input('confirm') !== 'DELETE') {
            return back()->with('error', 'You must type DELETE to confirm.');
        }

        DB::table('inventory')->truncate();

        return back()->with('success', 'Inventory database cleared successfully.');
    }
}
