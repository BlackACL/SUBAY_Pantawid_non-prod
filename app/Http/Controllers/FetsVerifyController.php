<?php

namespace App\Http\Controllers\Provincial;

use App\Http\Controllers\Controller;
use App\Models\FetsDocument;
use Illuminate\Http\Request;

class FetsVerifyController extends Controller
{
    public function show($id)
    {
        $fets = FetsDocument::with('user')->findOrFail($id);

        // optional: restrict actions (but still allow submitter to view)
        // if (! auth()->user()->hasRole('provincial') && auth()->id() !== $fets->user_id) {
        //     abort(403);
        // }

         activity()
        ->causedBy(auth()->user()) // who did the action
        ->performedOn($fets) // what model was acted on
        ->withProperties([
            'document_title' => $fets->title ?? 'No title',
            'user_name' => auth()->user()->name
        ])
        ->log('Viewed FETS document');

        return view('provincial.fets.verify', compact('fets'));
    }
}
