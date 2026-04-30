<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use Illuminate\View\View;

class BoqController extends Controller
{
    public function index(): View
    {
        return view('admin.boqs.index');
    }

    public function show(string $uuid): View
    {
        $boq = Boq::where('uuid', $uuid)->with(['project', 'client', 'items.unit'])->firstOrFail();
        return view('admin.boqs.show', compact('boq'));
    }
}
