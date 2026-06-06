<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestBoqController extends Controller
{
    public function create(Request $request): View
    {
        // Generate (or retrieve) a persistent guest token for this browser session.
        // The token links the guest BOQ records back to this session so they can
        // be claimed when the guest eventually registers or logs in.
        $guestToken = $request->session()->get('guest_boq_token');

        if (! $guestToken) {
            $guestToken = (string) Str::uuid();
            $request->session()->put('guest_boq_token', $guestToken);
        }

        return view('guest.boq-try', compact('guestToken'));
    }
}
