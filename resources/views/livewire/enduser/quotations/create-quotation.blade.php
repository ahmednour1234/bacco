<div
    x-data="{
        dragOver: false,
        deleteConfirm: null,
        toast: null,
        selectedFileName: null,
        selectedFileSize: null,
        tempUploading: false,
        uploadReady: false,
        showToast(message, type = 'success') {
            this.toast = { message, type };
            setTimeout(() => this.toast = null, 4000);
        },
        startUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.selectedFileName = file.name;
            this.selectedFileSize = (file.size / 1024).toFixed(1);
            this.tempUploading = true;
            this.uploadReady = false;
            $wire.upload(
                'boqFile',
                file,
                () => { this.tempUploading = false; this.uploadReady = true; },
                () => { this.tempUploading = false; this.uploadReady = false; this.selectedFileName = null; this.selectedFileSize = null; this.showToast('File upload failed. Please try again.', 'error'); },
                () => {}
            );
        }
    }"
    x-init="
        @if(session('success')) showToast('{{ session('success') }}', 'success') @endif
        @if(session('error'))   showToast('{{ session('error') }}',   'error')   @endif
        @if(session('warning')) showToast('{{ session('warning') }}', 'warning') @endif
    "
    x-on:toast.window="showToast($event.detail.message, $event.detail.type)"
>

    {{-- ───── Toast notification ──────────────────────────────────────────── --}}
    <div
        x-show="toast !== null"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-2xl px-5 py-3.5 shadow-lg text-sm font-medium"
        :class="{
            'bg-emerald-50 text-emerald-700 border border-emerald-200': toast?.type === 'success',
            'bg-red-50 text-red-700 border border-red-200':             toast?.type === 'error',
            'bg-amber-50 text-amber-700 border border-amber-200':       toast?.type === 'warning',
        }"
    >
        <span x-text="toast?.message"></span>
        <button @click="toast = null" class="ml-1 opacity-60 hover:opacity-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ───── Loading overlay ───────────────────────────────────────────────── --}}
    <div
        wire:loading.flex
        class="fixed inset-0 z-40 flex items-center justify-center bg-white/60 backdrop-blur-sm"
    >
        <div class="flex flex-col items-center gap-3">
            <svg class="h-8 w-8 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-sm font-medium text-slate-600" wire:loading.attr="class">Processing…</span>
        </div>
    </div>

    <div class="space-y-6">

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- Section 1: Quotation Information                                    --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">Section 1: Quotation Information</h2>
            </div>

            <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Project Name
                    </label>
                    <input
                        type="text"
                        wire:model="projectName"
                        placeholder="e.g., Al-Majd Tower Refurbishment"
                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition
                            @error('projectName') border-red-400 focus:ring-2 focus:ring-red-100
                            @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"
                    >
                    @error('projectName')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Project Status
                    </label>
                    <select
                        wire:model="projectStatus"
                        class="h-11 w-full rounded-xl border bg-white px-4 text-sm text-slate-700 shadow-sm outline-none transition
                            @error('projectStatus') border-red-400 focus:ring-2 focus:ring-red-100
                            @else border-slate-200 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 @enderror"
                    >
                        <option value="">Select status…</option>
                        @foreach($projectStatuses as $ps)
                            <option value="{{ $ps->value }}" @selected($projectStatus === $ps->value)>
                                {{ $ps->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('projectStatus')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- Section 2: BOQ Upload & Management                                  --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">Section 2: BOQ Upload &amp; Management</h2>
            </div>

            <div class="p-6 space-y-6">

                {{-- Sub-section A: Upload --}}
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Sub-Section A: Upload BOQ File
                    </p>

                    <label
                        for="boq-upload"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="
                            dragOver = false;
                            if ($event.dataTransfer.files.length) {
                                const dt = new DataTransfer();
                                dt.items.add($event.dataTransfer.files[0]);
                                const input = document.getElementById('boq-upload');
                                input.files = dt.files;
                                startUpload({ target: input });
                            }
                        "
                        :class="dragOver ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:border-emerald-300 hover:bg-emerald-50/40'"
                        class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed px-6 py-12 text-center transition"
                    >
                        {{-- Upload / spinner icon --}}
                        <div class="relative">
                            <svg x-show="!tempUploading" class="h-10 w-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <svg x-show="tempUploading" x-cloak class="h-10 w-10 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>

                        {{-- Dynamic label text (Alpine-driven for immediate feedback) --}}
                        <template x-if="selectedFileName">
                            <span class="text-sm font-medium text-emerald-600">
                                <span x-text="selectedFileName"></span>
                                <span class="text-slate-400" x-text="' (' + selectedFileSize + ' KB)'"></span>
                            </span>
                        </template>
                        <template x-if="selectedFileName && tempUploading">
                            <span class="text-xs text-slate-400">Uploading… please wait.</span>
                        </template>
                        <template x-if="selectedFileName && uploadReady">
                            <span class="text-xs text-slate-400">File ready. Click "Extract Items" to proceed.</span>
                        </template>
                        <template x-if="!selectedFileName">
                            <div class="space-y-1">
                                @if($boqFileName)
                                    <span class="block text-sm font-medium text-slate-600">{{ $boqFileName }}</span>
                                    <span class="block text-xs text-slate-400">Previously uploaded. Select a new file to re-extract.</span>
                                @else
                                    <span class="block text-sm font-medium text-slate-700">Click to upload or drag and drop</span>
                                    <span class="block text-xs text-slate-400">Excel (.xlsx), CSV, and PDF supported. Max: 50MB</span>
                                @endif
                            </div>
                        </template>

                        <input
                            id="boq-upload"
                            type="file"
                            @change="startUpload($event)"
                            accept=".pdf,.xlsx,.xls,.csv"
                            class="hidden"
                        >
                    </label>

                    @error('boqFile')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Extract button: only enabled after $wire.upload() callback confirms $boqFile is committed --}}
                    <div x-show="uploadReady || {{ $boqFileName ? 'true' : 'false' }}" x-cloak class="mt-3 flex justify-end">
                        <button
                            type="button"
                            wire:click="uploadBoq"
                            wire:loading.attr="disabled"
                            wire:target="uploadBoq"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="uploadBoq" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span wire:loading.remove wire:target="uploadBoq">Extract Items via AI</span>
                            <span wire:loading wire:target="uploadBoq">Extracting…</span>
                        </button>
                    </div>
                </div>

                {{-- Sub-section B: Item Table --}}
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Sub-Section B: Add Item Manually
                        </p>
                        <button
                            type="button"
                            wire:click="addManualItem"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add New Row
                        </button>
                    </div>

                    @if(empty($items))
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 py-10 text-center text-sm text-slate-400">
                            No items yet. Upload a BOQ file or add items manually.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50">
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 min-w-[200px]">Description</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-20">QTY</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">Unit</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-32">Category</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-36">Brand</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-24">Engineering</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 w-28">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($items as $index => $item)
                                        <tr class="group transition-colors hover:bg-slate-50/60
                                            @if(($item['status'] ?? '') === 'rejected') opacity-60 @endif">

                                            {{-- Description --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['description'] }}"
                                                    wire:change="updateItem({{ $index }}, 'description', $event.target.value)"
                                                    placeholder="Item description…"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Quantity --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="number"
                                                    value="{{ $item['quantity'] }}"
                                                    wire:change="updateItem({{ $index }}, 'quantity', $event.target.value)"
                                                    min="0"
                                                    step="any"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Unit --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['unit'] }}"
                                                    wire:change="updateItem({{ $index }}, 'unit', $event.target.value)"
                                                    placeholder="pcs"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Category --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['category'] }}"
                                                    wire:change="updateItem({{ $index }}, 'category', $event.target.value)"
                                                    placeholder="Category"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Brand --}}
                                            <td class="px-4 py-2.5">
                                                <input
                                                    type="text"
                                                    value="{{ $item['brand'] }}"
                                                    wire:change="updateItem({{ $index }}, 'brand', $event.target.value)"
                                                    placeholder="Brand"
                                                    class="w-full rounded-lg border border-transparent bg-transparent px-2 py-1 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-1 focus:ring-emerald-200 group-hover:border-slate-200"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Status badge --}}
                                            <td class="px-4 py-2.5">
                                                @php
                                                    $statusVal = $item['status'] ?? 'pending';
                                                    $badgeClass = match($statusVal) {
                                                        'sourcing' => 'bg-emerald-100 text-emerald-700',
                                                        'sourced'  => 'bg-blue-100  text-blue-700',
                                                        'rejected' => 'bg-red-100   text-red-700',
                                                        default    => 'bg-amber-100 text-amber-700',
                                                    };
                                                    $badgeLabel = match($statusVal) {
                                                        'sourcing' => 'Confirmed',
                                                        'sourced'  => 'Sourced',
                                                        'rejected' => 'Rejected',
                                                        default    => 'Pending',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                    {{ $badgeLabel }}
                                                </span>
                                            </td>

                                            {{-- Engineering checkbox --}}
                                            <td class="px-4 py-2.5 text-center">
                                                <input
                                                    type="checkbox"
                                                    @checked(!empty($item['engineering_required']))
                                                    wire:change="updateItem({{ $index }}, 'engineering_required', $event.target.checked)"
                                                    class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                    @if(($item['status'] ?? '') === 'rejected') disabled @endif
                                                >
                                            </td>

                                            {{-- Actions --}}
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @if(($item['status'] ?? 'pending') === 'pending')
                                                        {{-- Approve --}}
                                                        <button
                                                            type="button"
                                                            wire:click="approveItem({{ $index }})"
                                                            title="Approve"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </button>
                                                        {{-- Reject --}}
                                                        <button
                                                            type="button"
                                                            wire:click="rejectItem({{ $index }})"
                                                            title="Reject"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    @elseif(($item['status'] ?? '') === 'sourcing')
                                                        {{-- Undo approve back to pending --}}
                                                        <button
                                                            type="button"
                                                            wire:click="rejectItem({{ $index }})"
                                                            title="Reject"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    @elseif(($item['status'] ?? '') === 'rejected')
                                                        {{-- Restore to pending --}}
                                                        <button
                                                            type="button"
                                                            wire:click="approveItem({{ $index }})"
                                                            title="Restore"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-600"
                                                        >
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    {{-- Delete --}}
                                                    <button
                                                        type="button"
                                                        wire:click="deleteItem({{ $index }})"
                                                        wire:confirm="Delete this item?"
                                                        title="Delete"
                                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────────────────── --}}
        {{-- Section 3: Review & Submit                                          --}}
        {{-- ─────────────────────────────────────────────────────────────────── --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-slate-800">Section 3: Review &amp; Submit</h2>
            </div>

            <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">

                {{-- Summary cards --}}
                <div class="flex flex-wrap gap-6">

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Items</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ count($items) }}</p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $projectStatus
                                ? \App\Enums\QuotationProjectStatusEnum::tryFrom($projectStatus)?->label() ?? $projectStatus
                                : '—' }}
                        </p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Project Name</p>
                        <p class="mt-1 max-w-[180px] truncate text-sm font-semibold text-slate-700">
                            {{ $projectName ?: '—' }}
                        </p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">BOQ Attachment</p>
                        <p class="mt-1 text-sm font-semibold {{ $boqFileName ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $boqFileName ?: 'No file' }}
                        </p>
                    </div>

                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="saveDraft" class="h-4 w-4 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Save Draft
                    </button>

                    <button
                        type="button"
                        wire:click="submit"
                        wire:loading.attr="disabled"
                        @if($processing) disabled @endif
                        class="inline-flex h-11 items-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="submit" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Submit Quotation &rarr;
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>
