<form wire:submit="save" class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.name') }}</label>
                <input type="text" wire:model.blur="name" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.slug_optional') }}</label>
                <input type="text" wire:model.blur="slug" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.parent_category') }}</label>
                <select wire:model="parent_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">{{ __('app.no_parent') }}</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
                @error('parent_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('app.description') }}</label>
                <textarea wire:model.blur="description" rows="4" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="active" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>{{ __('app.active') }}</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="inline-flex items-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
            {{ $isEditing ? __('app.update_category') : __('app.create_category') }}
        </button>
        <a href="{{ route('admin.categories.index') }}" wire:navigate class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
            {{ __('app.cancel') }}
        </a>
    </div>
</form>
