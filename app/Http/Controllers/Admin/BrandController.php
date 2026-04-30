<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Exports\BrandsTemplateExport;
use App\Imports\BrandsImport;
use App\Models\Brand;
use App\Services\Admin\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class BrandController extends Controller
{
    public function __construct(
        private readonly BrandService $brandService
    ) {}

    public function index(): View
    {
        return view('admin.brands.index');
    }

    public function create(): View
    {
        return view('admin.brands.create');
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $this->brandService->create($request->validated());

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brandService->update($brand, $request->validated());

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->brandService->delete($brand);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new BrandsImport();
        Excel::import($import, $request->file('file'));

        return redirect()->route('admin.brands.index')
            ->with('success', $import->imported . ' brands imported successfully.');
    }

    public function template()
    {
        return Excel::download(new BrandsTemplateExport(), 'brands_template.xlsx');
    }
}
