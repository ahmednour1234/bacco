@extends('layouts.admin-app')

@section('title', 'Import #' . $import->id)

@section('content')
<div class="p-6 space-y-6">

    {{-- Back + title + run queue --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.catalog.imports.index') }}"
               class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Import #{{ $import->id }}</h1>
            @php
                $statusColors = [
                    'pending'    => 'bg-yellow-100 text-yellow-800',
                    'processing' => 'bg-blue-100 text-blue-800',
                    'completed'  => 'bg-green-100 text-green-800',
                    'failed'     => 'bg-red-100 text-red-800',
                ];
                $color = $statusColors[$import->status] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span id="status-badge"
                  class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $color }}">
                {{ ucfirst($import->status) }}
            </span>
        </div>

        @if(in_array($import->status, ['pending', 'failed']))
        <form method="POST" action="{{ route('admin.catalog.queue.run') }}">
            @csrf
            <button type="submit"
                    onclick="this.disabled=true; this.innerHTML='<svg class=\'animate-spin h-4 w-4\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8v8z\'></path></svg> Starting…'; this.form.submit();"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/>
                </svg>
                Run Queue
            </button>
        </form>
        @endif
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Processed', 'value' => $import->processed_rows, 'color' => 'text-gray-800'],
            ['label' => 'Inserted',  'value' => $import->inserted_rows,  'color' => 'text-green-700'],
            ['label' => 'Updated',   'value' => $import->updated_rows,   'color' => 'text-blue-700'],
            ['label' => 'Failed',    'value' => $import->failed_rows,    'color' => 'text-red-700'],
        ] as $stat)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ $stat['label'] }}</p>
                <p id="stat-{{ strtolower($stat['label']) }}"
                   class="text-2xl font-bold mt-1 {{ $stat['color'] }}">
                    {{ number_format($stat['value']) }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- Progress bar --}}
    @if($import->total_rows > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Progress</span>
                <span id="percent-label">{{ $import->progressPercent() }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div id="progress-bar"
                     class="bg-green-500 h-3 rounded-full transition-all duration-500"
                     style="width: {{ $import->progressPercent() }}%"></div>
            </div>
        </div>
    @endif

    {{-- Details --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm divide-y divide-gray-100">
            <tbody class="divide-y divide-gray-100">
                @foreach([
                    ['File',        $import->file_name],
                    ['Catalog',     $import->catalog?->name ?? '—'],
                    ['Uploaded at', $import->created_at->format('Y-m-d H:i:s')],
                    ['Started at',  $import->started_at?->format('Y-m-d H:i:s') ?? '—'],
                    ['Finished at', $import->finished_at?->format('Y-m-d H:i:s') ?? '—'],
                ] as [$label, $val])
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-500 w-36">{{ $label }}</td>
                        <td class="px-5 py-3 text-gray-800">{{ $val }}</td>
                    </tr>
                @endforeach
                @if($import->error_message)
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-500">Error</td>
                        <td class="px-5 py-3 text-red-700 font-mono text-xs break-all">{{ $import->error_message }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($import->failed_rows > 0)
        <a href="{{ route('admin.catalog.imports.failed-rows', $import->id) }}"
           class="inline-flex items-center gap-2 text-sm text-red-600 hover:underline font-medium">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            View {{ number_format($import->failed_rows) }} failed rows
        </a>
    @endif
</div>

{{-- Auto-refresh when pending/processing --}}
@if(in_array($import->status, ['pending', 'processing']))
<script>
(function poll() {
    setTimeout(async () => {
        try {
            const res  = await fetch('{{ route('admin.catalog.imports.progress', $import->id) }}');
            const data = await res.json();

            document.getElementById('stat-processed').textContent = data.processed_rows.toLocaleString();
            document.getElementById('stat-inserted').textContent  = data.inserted_rows.toLocaleString();
            document.getElementById('stat-updated').textContent   = data.updated_rows.toLocaleString();
            document.getElementById('stat-failed').textContent    = data.failed_rows.toLocaleString();

            const badge = document.getElementById('status-badge');
            badge.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);

            const bar  = document.getElementById('progress-bar');
            const pct  = document.getElementById('percent-label');
            if (bar) { bar.style.width = data.percent + '%'; }
            if (pct) { pct.textContent = data.percent + '%'; }

            if (data.status === 'pending' || data.status === 'processing') {
                poll();
            } else {
                window.location.reload();
            }
        } catch (_) { poll(); }
    }, 3000);
})();
</script>
@endif
@endsection
