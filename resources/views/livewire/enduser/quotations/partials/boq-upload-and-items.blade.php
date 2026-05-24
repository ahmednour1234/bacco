{{-- BOQ Upload and Items Section --}}
<div>
    <form wire:submit.prevent="uploadBoqFile">
        <label class="block mb-2 font-semibold">رفع ملف جدول الكميات (BOQ)</label>
        <input type="file" wire:model="boqFile" accept=".xlsx,.xls,.csv" class="mb-2" />
        @error('boqFile')
            <div class="text-red-500 text-xs mb-2">{{ $message }}</div>
        @enderror
        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded">رفع الملف</button>
    </form>

    @if(session('boq_upload_success'))
        <div class="text-green-600 mt-2">{{ session('boq_upload_success') }}</div>
    @endif

    @if(!empty($boqItems))
        <div class="mt-6">
            <h3 class="font-semibold mb-2">المنتجات المستخرجة من الملف:</h3>
            <table class="min-w-full border text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="border px-2 py-1">الوصف</th>
                        <th class="border px-2 py-1">الكمية</th>
                        <th class="border px-2 py-1">الوحدة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boqItems as $item)
                        <tr>
                            <td class="border px-2 py-1">{{ $item['description'] ?? '' }}</td>
                            <td class="border px-2 py-1">{{ $item['quantity'] ?? '' }}</td>
                            <td class="border px-2 py-1">{{ $item['unit'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>