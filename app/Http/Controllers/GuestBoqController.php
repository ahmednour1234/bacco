<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestBoqController extends Controller
{
    public function create(Request $request): View
    {
        $guestToken = $request->session()->get('guest_boq_token');

        if (! $guestToken) {
            $guestToken = (string) Str::uuid();
            $request->session()->put('guest_boq_token', $guestToken);
        }

        return view('guest.boq-try', compact('guestToken'));
    }
}
