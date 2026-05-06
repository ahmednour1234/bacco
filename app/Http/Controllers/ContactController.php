<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:120'],
            'email'        => ['required', 'email', 'max:180'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'company'      => ['nullable', 'string', 'max:120'],
            'role'         => ['nullable', 'string', 'max:100'],
            'inquiry_type' => ['nullable', 'string', 'max:80'],
            'message'      => ['required', 'string', 'max:3000'],
        ]);

        ContactSubmission::create(array_merge($validated, [
            'ip_address' => $request->ip(),
        ]));

        return back()->with('success', __('contact.form.success'));
    }
}
