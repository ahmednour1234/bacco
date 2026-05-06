<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $query = ContactSubmission::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('company', 'like', "%{$q}%")
                  ->orWhere('message', 'like', "%{$q}%");
            });
        }

        $submissions = $query->paginate(25)->withQueryString();
        $counts = [
            'all'     => ContactSubmission::count(),
            'new'     => ContactSubmission::where('status', 'new')->count(),
            'read'    => ContactSubmission::where('status', 'read')->count(),
            'replied' => ContactSubmission::where('status', 'replied')->count(),
        ];

        return view('admin.contact-submissions.index', compact('submissions', 'counts'));
    }

    public function show(ContactSubmission $contactSubmission): View
    {
        $contactSubmission->markAsRead();

        return view('admin.contact-submissions.show', compact('contactSubmission'));
    }

    public function updateStatus(Request $request, ContactSubmission $contactSubmission): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:new,read,replied'],
        ]);

        $contactSubmission->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(ContactSubmission $contactSubmission): RedirectResponse
    {
        $contactSubmission->delete();

        return redirect()->route('admin.contact-submissions.index')
            ->with('success', 'Submission deleted.');
    }
}
