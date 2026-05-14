<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $clientId = Auth::id();

        $allProjectsQuery = Project::where('client_id', $clientId);
        $stats = [
            'total'     => (clone $allProjectsQuery)->count(),
            'active'    => (clone $allProjectsQuery)->where('status', 'active')->count(),
            'completed' => (clone $allProjectsQuery)->where('status', 'completed')->count(),
            'pending'   => (clone $allProjectsQuery)->where('status', 'pending')->count(),
            'cancelled' => (clone $allProjectsQuery)->where('status', 'cancelled')->count(),
        ];

        $search  = $request->input('search', '');
        $status  = $request->input('status', '');
        $perPage = in_array((int) $request->input('per_page', 10), [5, 10, 25, 50])
            ? (int) $request->input('per_page', 10)
            : 10;

        $query = Project::query()
            ->withCount(['boqs', 'quotationRequests', 'orders'])
            ->where('client_id', $clientId)
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $projects = $query->paginate($perPage)->withQueryString();

        return view('enduser.projects.index', compact(
            'stats', 'projects', 'search', 'status', 'perPage'
        ));
    }

    public function show(string $uuid): View
    {
        $project = Project::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        return view('enduser.projects.show', compact('project'));
    }
}
