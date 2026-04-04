<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('supplier.products.index');
    }

    public function create(): View
    {
        return view('supplier.products.create');
    }

    public function edit(SupplierProduct $supplierProduct): View
    {
        abort_unless($supplierProduct->supplier_id === Auth::id(), 403);

        return view('supplier.products.edit', compact('supplierProduct'));
    }
}
