<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Repositories\Enduser\OrderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderRepository $repo) {}

    public function index(): View
    {
        return view('enduser.orders.index');
    }

    public function show(string $uuid): View
    {
        $order = $this->repo->findByUuidForClient($uuid, Auth::id());

        abort_if($order === null, 404);

        return view('enduser.orders.show', compact('order'));
    }
}
