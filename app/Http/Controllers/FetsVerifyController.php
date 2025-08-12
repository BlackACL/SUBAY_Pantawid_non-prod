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

        return view('provincial.fets.verify', compact('fets'));
    }
}
