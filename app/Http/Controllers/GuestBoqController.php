<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestBoqController extends Controller
{
    public function create(Request $request): View
    {
        // Force Arabic for the /try page
        app()->setLocale('ar');

        $guestToken = $request->session()->get('guest_boq_token');

        if (! $guestToken) {
            $guestToken = (string) Str::uuid();
            $request->session()->put('guest_boq_token', $guestToken);
        }

        return view('guest.boq-try', compact('guestToken'));
    }
}
