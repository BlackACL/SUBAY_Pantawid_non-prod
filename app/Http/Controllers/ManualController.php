<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Manual;
use Illuminate\Support\Facades\Storage;

class ManualController extends Controller
{
    /**
     * View the current manual (for all users)
     */
    public function view()
    {
        // Only get active (non-deleted) manuals
        $manual = Manual::whereNull('deleted_at')->latest('uploaded_at')->first();
        
        if (!$manual || !Storage::disk('public')->exists('manuals/' . $manual->filename)) {
            abort(404, 'Manual not found. Please contact administrator.');
        }

        $path = storage_path('app/public/manuals/' . $manual->filename);
        return response()->file($path);
    }

    /**
     * Upload a new manual (superadmin only)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'manual_file' => 'required|file|mimes:pdf|max:102400' // Max 100MB
        ]);

        $file = $request->file('manual_file');
        $filename = 'user_manual_' . time() . '.pdf';
        
        // Store the file
        $file->storeAs('manuals', $filename, 'public');

        // Soft delete the old active manual (keeps file for recovery)
        $oldManual = Manual::whereNull('deleted_at')->latest('uploaded_at')->first();
        if ($oldManual) {
            $oldManual->delete(); // Soft delete, file remains
        }

        // Save new manual record
        Manual::create([
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_at' => now()
        ]);

        return redirect()->route('officials.index', ['tab' => 'manual'])
            ->with('success', 'Manual uploaded successfully!');
    }

    /**
     * Delete the current manual (superadmin only)
     */
    public function delete()
    {
        // Only get active (non-deleted) manuals
        $manual = Manual::whereNull('deleted_at')->latest('uploaded_at')->first();
        
        if (!$manual) {
            return redirect()->route('officials.index', ['tab' => 'manual'])
                ->with('error', 'No manual found to delete.');
        }

        // Soft delete - file remains in storage for recovery
        // Physical file will stay at: storage/app/public/manuals/{filename}
        $manual->delete();

        return redirect()->route('officials.index', ['tab' => 'manual'])
            ->with('success', 'Manual deleted successfully! (File kept for recovery)');
    }

    /**
     * Restore a soft-deleted manual
     */
    public function restore($id)
    {
        $manual = Manual::withTrashed()->findOrFail($id);
        
        if (!$manual->trashed()) {
            return redirect()->route('officials.index', ['tab' => 'manual'])
                ->with('error', 'Manual is not deleted.');
        }

        $manual->restore();

        return redirect()->route('officials.index', ['tab' => 'manual'])
            ->with('success', 'Manual restored successfully!');
    }

    /**
     * Permanently delete a manual and its file
     */
    public function forceDelete($id)
    {
        $manual = Manual::withTrashed()->findOrFail($id);

        // Delete physical file
        if (Storage::disk('public')->exists('manuals/' . $manual->filename)) {
            Storage::disk('public')->delete('manuals/' . $manual->filename);
        }

        // Permanently delete from database
        $manual->forceDelete();

        return redirect()->route('officials.index', ['tab' => 'manual'])
            ->with('success', 'Manual permanently deleted!');
    }

    /**
     * Show trashed/deleted manuals
     */
    public function trashed()
    {
        $manuals = Manual::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        
        return view('manuals.trashed', compact('manuals'));
    }
}

