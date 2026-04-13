<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BoqController extends Controller
{
    public function index(): View
    {
        return view('enduser.boqs.index');
    }

    public function create(?string $projectUuid = null): View
    {
        return view('enduser.boqs.create', compact('projectUuid'));
    }

    public function show(string $uuid): View
    {
        $boq = Boq::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->firstOrFail();

        return view('enduser.boqs.show', compact('boq'));
    }
}
