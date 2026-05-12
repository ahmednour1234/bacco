<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.admins.index');
    }

    public function create(): View
    {
        return view('admin.admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|string|max:30',
            'user_type' => 'required|in:admin,employee',
            'password'  => ['required', Password::min(8)],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'user_type' => $data['user_type'],
            'password'  => Hash::make($data['password']),
            'active'    => true,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'تم إنشاء الحساب بنجاح.');
    }

    public function edit(User $admin): View
    {
        abort_unless(
            in_array($admin->user_type->value, ['admin', 'employee']),
            404
        );
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        abort_unless(
            in_array($admin->user_type->value, ['admin', 'employee']),
            404
        );

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $admin->id,
            'phone'     => 'nullable|string|max:30',
            'user_type' => 'required|in:admin,employee',
            'password'  => ['nullable', Password::min(8)],
        ]);

        // Prevent changing own role away from admin
        if ((int) $admin->id === (int) auth()->id() && $data['user_type'] !== 'admin') {
            return back()->withErrors(['user_type' => 'لا يمكنك تغيير صلاحيتك الخاصة.'])->withInput();
        }

        $update = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'user_type' => $data['user_type'],
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $admin->update($update);

        return redirect()->route('admin.admins.index')
            ->with('success', 'تم تحديث الحساب بنجاح.');
    }
}
