<div>
    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="relative w-full sm:w-80">
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Search by name, email..."
                        class="h-8 w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder-slate-400 outline-none focus:ring-0">
                    @if($search !== '')
                        <button type="button" wire:click="$set('search', '')"
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Status filter --}}
            <select wire:model.live="status"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        {{-- Add button (admin only) --}}
        @if(auth()->user()->user_type->value === 'admin')
            <button wire:click="openCreate"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Admin / Employee
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="w-[25%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                        <th class="w-[28%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                        <th class="w-[15%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</th>
                        <th class="w-[12%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                        <th class="w-[10%] px-5 py-3.5 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="w-[10%] px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($admins as $admin)
                        <tr class="group transition hover:bg-slate-50/60">
                            {{-- Name --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                                        {{ $admin->user_type->value === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}
                                        text-xs font-bold">
                                        {{ strtoupper(substr($admin->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $admin->name }}</p>
                                        @if((int) $admin->id === (int) auth()->id())
                                            <span class="text-xs text-slate-400">(you)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="px-5 py-4 text-sm text-slate-700 truncate">{{ $admin->email }}</td>

                            {{-- Phone --}}
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $admin->phone ?: '—' }}</td>

                            {{-- Role --}}
                            <td class="px-5 py-4">
                                @if($admin->user_type->value === 'admin')
                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-1 text-xs font-semibold text-purple-700">Admin</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">Employee</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if($admin->active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4">
                                @if(auth()->user()->user_type->value === 'admin')
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Edit --}}
                                        <button wire:click="openEdit({{ $admin->id }})"
                                            class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        {{-- Toggle active (can't deactivate yourself) --}}
                                        @if((int) $admin->id !== (int) auth()->id())
                                            @if($admin->active)
                                                <button wire:click="toggleActive({{ $admin->id }})"
                                                    class="inline-flex items-center rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                                    Deactivate
                                                </button>
                                            @else
                                                <button wire:click="toggleActive({{ $admin->id }})"
                                                    class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                    Activate
                                                </button>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-400">—</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="block text-center text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="mt-2 text-sm font-medium text-slate-500">No admins found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">{{ $admins->links() }}</div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeModal"></div>

            {{-- Panel --}}
            <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-bold text-slate-900">
                        {{ $editingId ? 'Edit Admin / Employee' : 'Add Admin / Employee' }}
                    </h2>
                    <button wire:click="closeModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="px-6 py-5 space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('name') border-red-400 @enderror"
                            placeholder="Full name">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Email <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('email') border-red-400 @enderror"
                            placeholder="email@qimta.com">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Phone</label>
                        <input wire:model="phone" type="text"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                            placeholder="+966...">
                    </div>

                    {{-- Role --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Role <span class="text-red-500">*</span></label>
                        <select wire:model="userType"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('userType') border-red-400 @enderror">
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('userType') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">
                            Password {{ $editingId ? '(leave blank to keep current)' : '*' }}
                        </label>
                        <input wire:model="password" type="password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 @error('password') border-red-400 @enderror"
                            placeholder="Min 8 characters">
                        @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" wire:click="closeModal"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $editingId ? 'Save Changes' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
