<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('admin.suppliers.index');
    }

    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone'        => ['required', 'string', 'max:30'],
            'password'     => ['required', 'string', 'min:8'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'division'     => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'city'         => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'],
                'password'  => Hash::make($data['password']),
                'user_type' => UserTypeEnum::Supplier,
                'active'    => true,
            ]);

            SupplierProfile::create([
                'user_id'      => $user->id,
                'company_name' => $data['company_name'] ?? null,
                'division'     => $data['division'] ?? null,
                'address'      => $data['address'] ?? null,
                'city'         => $data['city'] ?? null,
                'country'      => $data['country'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier account created successfully.');
    }

    public function show(string $uuid): View
    {
        $supplier = User::with('supplierProfile')
            ->where('uuid', $uuid)
            ->where('user_type', UserTypeEnum::Supplier)
            ->withCount('supplierProducts')
            ->firstOrFail();

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(string $uuid): View
    {
        $supplier = User::with('supplierProfile')
            ->where('uuid', $uuid)
            ->where('user_type', UserTypeEnum::Supplier)
            ->firstOrFail();

        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $uuid): RedirectResponse
    {
        $supplier = User::with('supplierProfile')
            ->where('uuid', $uuid)
            ->where('user_type', UserTypeEnum::Supplier)
            ->firstOrFail();

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', "unique:users,email,{$supplier->id}"],
            'phone'        => ['required', 'string', 'max:30'],
            'password'     => ['nullable', 'string', 'min:8'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'division'     => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'city'         => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($supplier, $data): void {
            $userUpdate = [
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ];
            if (! empty($data['password'])) {
                $userUpdate['password'] = Hash::make($data['password']);
            }
            $supplier->update($userUpdate);

            $supplier->supplierProfile()->updateOrCreate(
                ['user_id' => $supplier->id],
                [
                    'company_name' => $data['company_name'] ?? null,
                    'division'     => $data['division'] ?? null,
                    'address'      => $data['address'] ?? null,
                    'city'         => $data['city'] ?? null,
                    'country'      => $data['country'] ?? null,
                ]
            );
        });

        return redirect()
            ->route('admin.suppliers.show', $supplier->uuid)
            ->with('success', 'Supplier updated successfully.');
    }

    public function toggleStatus(string $uuid): RedirectResponse
    {
        $supplier = User::where('uuid', $uuid)
            ->where('user_type', UserTypeEnum::Supplier)
            ->firstOrFail();

        $supplier->update(['active' => ! $supplier->active]);

        $label = $supplier->active ? 'activated' : 'deactivated';

        return back()->with('success', "Supplier {$label} successfully.");
    }
}
