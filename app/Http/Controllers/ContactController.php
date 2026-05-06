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
            'phone_local'  => ['nullable', 'string', 'max:20', 'regex:/^[0-9\s\-]+$/'],
            'company'      => ['nullable', 'string', 'max:120'],
            'role'         => ['nullable', 'string', 'max:100'],
            'inquiry_type' => ['nullable', 'string', 'max:80'],
            'message'      => ['required', 'string', 'max:3000'],
        ]);

        // Combine country code with local number
        $phone = null;
        if (!empty($validated['phone_local'])) {
            $local = preg_replace('/\D/', '', $validated['phone_local']);
            $phone = '+966' . $local;
        }

        ContactSubmission::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone'        => $phone,
            'company'      => $validated['company'] ?? null,
            'role'         => $validated['role'] ?? null,
            'inquiry_type' => $validated['inquiry_type'] ?? null,
            'message'      => $validated['message'],
            'ip_address'   => $request->ip(),
        ]);

        return back()->with('success', __('contact.form.success'));
    }
}
