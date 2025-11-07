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
        $manual = Manual::latest('uploaded_at')->first();
        
        if (!$manual || !Storage::disk('public')->exists('manuals/' . $manual->filename)) {
            abort(404, 'Manual not found. Please contact administrator.');
        }

        $path = Storage::disk('public')->path('manuals/' . $manual->filename);
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

        // Delete old manual file if exists
        $oldManual = Manual::latest('uploaded_at')->first();
        if ($oldManual && Storage::disk('public')->exists('manuals/' . $oldManual->filename)) {
            Storage::disk('public')->delete('manuals/' . $oldManual->filename);
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
        $manual = Manual::latest('uploaded_at')->first();
        
        if (!$manual) {
            return redirect()->route('officials.index', ['tab' => 'manual'])
                ->with('error', 'No manual found to delete.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists('manuals/' . $manual->filename)) {
            Storage::disk('public')->delete('manuals/' . $manual->filename);
        }

        // Delete record
        $manual->delete();

        return redirect()->route('officials.index', ['tab' => 'manual'])
            ->with('success', 'Manual deleted successfully!');
    }
}
