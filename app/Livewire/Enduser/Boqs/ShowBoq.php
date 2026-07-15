<?php

namespace App\Livewire\Enduser\Boqs;

use App\Enums\BoqStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\QuotationRequestStatusEnum;
use App\Enums\QuotationSourceTypeEnum;
use App\Enums\QuotationVersionStatusEnum;
use App\Jobs\FetchQuotationPricesJob;
use App\Models\Boq;
use App\Models\BoqItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\QuotationVersion;
use App\Models\QuotationVersionItem;
use App\Models\Unit;
use App\Models\UploadedDocument;
use App\Services\NotificationService;
use App\Services\QuotationAiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class ShowBoq extends Component
{
    public string $uuid = '';

    public ?Boq $boq = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public bool $reprocessing = false;

    // ── Wizard state ──────────────────────────────────────────────────────────
    public int $currentStep = 1;

    public bool $pricesFetching = false;

    public ?int $quotationId = null;

    public string $quotationUuid = '';

    /** @var array<int, array<string, mixed>> */
    public array $pricedItems = [];

    public float $quotationTotal = 0;

    public int $pricedCount = 0;

    public int $unpricedCount = 0;

    // ── Address & payment (step 3) ────────────────────────────────────────────
    public string $deliveryAddressMode  = 'detailed';   // 'national' | 'detailed'
    public string $deliveryShortAddress = '';            // 8-char code e.g. RJHH6392
    public string $deliveryBuildingNo   = '';
    public string $deliveryStreet       = '';
    public string $deliveryDistrict     = '';
    public string $deliveryCity         = '';
    public string $deliveryRegion       = '';
    public string $deliveryPostalCode   = '';
    public string $deliveryAdditionalNo = '';
    public string $paymentMethod        = 'bank_transfer';

    // ── Order result (step 4) ─────────────────────────────────────────────────
    public string $orderUuid       = '';
    public string $orderNo         = '';
    public float  $orderGrandTotal = 0;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $this->boq = Boq::where('uuid', $uuid)
            ->where('client_id', Auth::id())
            ->with('project')
            ->firstOrFail();

        $this->loadItems();
    }

    // -------------------------------------------------------------------------
    // Item selection
    // -------------------------------------------------------------------------

    public function toggleSelected(int $itemId): void
    {
        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $newState = ! ($item['selected'] ?? false);
                $this->items[$index]['selected'] = $newState;
                BoqItem::where('id', $itemId)->update(['is_selected' => $newState]);
                break;
            }
        }
    }

    public function selectAll(): void
    {
        $ids = [];
        foreach ($this->items as $index => $item) {
            if (($item['status'] ?? '') !== 'rejected') {
                $this->items[$index]['selected'] = true;
                $ids[] = $item['id'];
            }
        }
        if (! empty($ids)) {
            BoqItem::whereIn('id', $ids)->update(['is_selected' => true]);
        }
    }

    public function deselectAll(): void
    {
        $ids = [];
        foreach ($this->items as $index => $_) {
            $this->items[$index]['selected'] = false;
            $ids[] = $this->items[$index]['id'];
        }
        if (! empty($ids)) {
            BoqItem::whereIn('id', $ids)->update(['is_selected' => false]);
        }
    }

    public function approveAll(): void
    {
        $ids = [];
        foreach ($this->items as $index => $item) {
            if (($item['status'] ?? '') !== 'rejected') {
                $this->items[$index]['status'] = 'sourcing';
                $ids[] = $item['id'];
            }
        }
        if (! empty($ids)) {
            BoqItem::whereIn('id', $ids)->update(['status' => 'sourcing']);
        }
        $this->dispatch('toast', message: 'All items approved successfully.', type: 'success');
    }

    // -------------------------------------------------------------------------
    // Inline item editing
    // -------------------------------------------------------------------------

    public function updateItem(int $index, string $field): void
    {
        $item = $this->items[$index] ?? null;
        if (! $item) {
            return;
        }

        $allowed = ['description', 'quantity', 'engineering_required'];
        if (! in_array($field, $allowed, true)) {
            return;
        }

        $value = $item[$field];

        if ($field === 'quantity') {
            $value = max(0, (float) $value);
            $this->items[$index]['quantity'] = $value;
        }

        BoqItem::where('id', $item['id'])->update([$field => $value]);
    }

    public function toggleEngineering(int $itemId): void
    {
        foreach ($this->items as $index => $item) {
            if ((int) $item['id'] === $itemId) {
                $newState = ! ($item['engineering_required'] ?? false);
                $this->items[$index]['engineering_required'] = $newState;
                BoqItem::where('id', $itemId)->update(['engineering_required' => $newState]);
                break;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Re-parse BOQ from the original uploaded file
    // -------------------------------------------------------------------------

    public function reparseBoq(): void
    {
        @set_time_limit(480);
        $this->reprocessing = true;

        try {
            $doc = UploadedDocument::where('boq_id', $this->boq->id)
                ->latest()
                ->first();

            if (! $doc || ! Storage::disk('local')->exists($doc->file_path)) {
                $this->dispatch('toast', message: 'Original file not found. Please upload the file again.', type: 'error');
                $this->reprocessing = false;
                return;
            }

            $absPath = Storage::disk('local')->path($doc->file_path);
            $ai      = app(QuotationAiService::class);
            $result  = $ai->parseBoq($absPath, [
                'boq_id'       => $this->boq->id,
                'project_name' => $this->boq->project?->name ?? '',
                'force_refresh' => true,
            ]);

            if (! $result['success']) {
                $this->dispatch('toast', message: $result['error'] ?? 'Re-extraction failed. Please try again.', type: 'error');
                $this->reprocessing = false;
                return;
            }

            // Wipe old items and persist fresh results.
            BoqItem::where('boq_id', $this->boq->id)->delete();

            foreach ($result['items'] ?? [] as $aiItem) {
                $item = array_merge([
                    'description'          => '',
                    'quantity'             => 1,
                    'unit_id'              => null,
                    'category'             => '',
                    'brand'                => '',
                    'status'               => 'pending',
                    'engineering_required' => false,
                    'confidence'           => null,
                    'unit_price'           => null,
                    'raw_data'             => null,
                    'ai_extracted'         => true,
                    'is_selected'          => false,
                ], $aiItem);

                BoqItem::create([
                    'boq_id'               => $this->boq->id,
                    'description'          => (string) ($item['description'] ?? ''),
                    'quantity'             => is_numeric($item['quantity']) ? (float) $item['quantity'] : 1,
                    'unit_id'              => $this->resolveUnitId($item['unit_id'] ?? null, $item['unit'] ?? null),
                    'category'             => (string) ($item['category'] ?? ''),
                    'brand'                => (string) ($item['brand'] ?? ''),
                    'status'               => $item['status'] ?? 'pending',
                    'engineering_required' => (bool) ($item['engineering_required'] ?? false),
                    'confidence'           => is_numeric($item['confidence'] ?? null) ? (float) $item['confidence'] : null,
                    'unit_price'           => is_numeric($item['unit_price'] ?? null) ? (float) $item['unit_price'] : null,
                    'raw_data'             => $item['raw_data'] ?? null,
                    'ai_extracted'         => true,
                    'is_selected'          => false,
                ]);
            }

            foreach ($result['rejected'] ?? [] as $rejItem) {
                $rawData = is_array($rejItem['raw_data'] ?? null) ? $rejItem['raw_data'] : [];
                BoqItem::create([
                    'boq_id'               => $this->boq->id,
                    'description'          => (string) ($rejItem['description'] ?? ''),
                    'quantity'             => is_numeric($rejItem['quantity'] ?? null) ? (float) $rejItem['quantity'] : 1,
                    'unit_id'              => $this->resolveUnitId($rejItem['unit_id'] ?? null, $rejItem['unit'] ?? null),
                    'category'             => (string) ($rejItem['category'] ?? ''),
                    'brand'                => (string) ($rejItem['brand'] ?? ''),
                    'status'               => 'rejected',
                    'engineering_required' => false,
                    'confidence'           => null,
                    'unit_price'           => null,
                    'raw_data'             => $rawData,
                    'ai_extracted'         => true,
                    'is_selected'          => false,
                ]);
            }

            $this->loadItems();

            $count         = count($result['items'] ?? []);
            $rejectedCount = count($result['rejected'] ?? []);
            $msg           = "Re-extracted {$count} supply item(s) from the file.";
            if ($rejectedCount > 0) {
                $msg .= " {$rejectedCount} item(s) were filtered out.";
            }

            $this->dispatch('toast', message: $msg, type: 'success');

        } catch (\Throwable $e) {
            Log::error('ShowBoq::reparseBoq failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Re-extraction failed. Please try again.', type: 'error');
        } finally {
            $this->reprocessing = false;
        }
    }

    // -------------------------------------------------------------------------
    // Create Quotation from selected items → Step 1 → 2
    // -------------------------------------------------------------------------

    public function confirmItems(): void
    {
        $selectedItems = array_values(array_filter($this->items, fn($i) => $i['selected'] ?? false));

        if (empty($selectedItems)) {
            $this->dispatch('toast', message: 'يرجى تحديد عنصر واحد على الأقل للتسعير.', type: 'error');
            return;
        }

        try {
            DB::transaction(function () use ($selectedItems) {
                $quotation = QuotationRequest::create([
                    'client_id'    => Auth::id(),
                    'project_id'   => $this->boq->project_id,
                    'boq_id'       => $this->boq->id,
                    'quotation_no' => $this->generateQuotationNo(),
                    'project_name' => $this->boq->project?->name,
                    'status'       => QuotationRequestStatusEnum::Tender,
                    'source_type'  => QuotationSourceTypeEnum::Boq,
                ]);

                $this->quotationId   = $quotation->id;
                $this->quotationUuid = $quotation->uuid;

                foreach ($selectedItems as $row) {
                    $unitName = trim((string) ($row['unit'] ?? ''));
                    $unitId   = $row['unit_id'] ?? null;
                    if ($unitId === null && $unitName !== '') {
                        $unitId = Unit::firstOrCreate(
                            ['name' => $unitName],
                            ['symbol' => mb_strtolower(mb_substr($unitName, 0, 20))]
                        )->id;
                    }

                    QuotationItem::create([
                        'quotation_request_id' => $quotation->id,
                        'product_id'           => $row['product_id'] ?? null,
                        'description'          => (string) ($row['description'] ?? ''),
                        'quantity'             => is_numeric($row['quantity'] ?? null) ? (float) $row['quantity'] : 0,
                        'unit_id'              => $unitId,
                        'unit_price'           => null,
                        'price_source'         => null,
                        'price_status'         => 'pending',
                        'category'             => (string) ($row['category'] ?? ''),
                        'brand'                => (string) ($row['brand'] ?? ''),
                        'status'               => 'pending',
                        'engineering_required' => (bool) ($row['engineering_required'] ?? false),
                        'confidence'           => is_numeric($row['confidence'] ?? null) ? (float) $row['confidence'] : null,
                        'ai_extracted'         => (bool) ($row['ai_extracted'] ?? false),
                        'is_selected'          => true,
                    ]);
                }

                // Load items into pricedItems (no prices yet)
                $this->pricedItems = QuotationItem::where('quotation_request_id', $quotation->id)
                    ->get()
                    ->map(fn($qi) => [
                        'description' => (string) $qi->description,
                        'quantity'    => (float) $qi->quantity,
                        'unit'        => $qi->unit?->name ?? '',
                        'brand'       => (string) ($qi->brand ?? ''),
                        'unit_price'  => null,
                        'line_total'  => 0,
                        'category'    => (string) ($qi->category ?? ''),
                    ])
                    ->toArray();

                $this->boq->update(['status' => BoqStatusEnum::Submitted]);
            });

            // Dispatch async pricing job
            FetchQuotationPricesJob::dispatch($this->quotationId, Auth::id(), $this->quotationUuid);

            $this->pricesFetching = true;
            $this->currentStep    = 2;

        } catch (\Throwable $e) {
            Log::error('ShowBoq::confirmItems failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'فشل إنشاء عرض السعر. يرجى المحاولة مرة أخرى.', type: 'error');
        }
    }

    // -------------------------------------------------------------------------
    // Step 2 polling — check when pricing job finishes
    // -------------------------------------------------------------------------

    public function pollPriceStatus(): void
    {
        if (! $this->pricesFetching || ! $this->quotationId) {
            return;
        }

        $quotation = QuotationRequest::find($this->quotationId);
        if (! $quotation || ! $quotation->prices_fetched_at) {
            return;
        }

        $refreshed   = QuotationItem::where('quotation_request_id', $this->quotationId)->with('unit')->get();
        $total       = 0;
        $pricedCount = 0;
        $unpriced    = 0;

        $this->pricedItems = [];

        foreach ($refreshed as $qi) {
            $unitPrice = is_numeric($qi->unit_price) && $qi->unit_price > 0 ? (float) $qi->unit_price : null;
            $quantity  = (float) $qi->quantity;
            $lineTotal = $unitPrice !== null ? round($unitPrice * $quantity, 2) : 0;

            $this->pricedItems[] = [
                'description'       => (string) $qi->description,
                'quantity'          => $quantity,
                'unit'              => $qi->unit?->name ?? '',
                'brand'             => (string) ($qi->brand ?? ''),
                'unit_price'        => $unitPrice,
                'line_total'        => $lineTotal,
                'category'          => (string) ($qi->category ?? ''),
                'price_verdict'     => $qi->price_verdict,
                'verification_note' => (string) ($qi->price_verification_note ?? ''),
            ];

            if ($unitPrice !== null) {
                $pricedCount++;
                $total += $lineTotal;
            } else {
                $unpriced++;
            }
        }

        $this->quotationTotal = round($total, 2);
        $this->pricedCount    = $pricedCount;
        $this->unpricedCount  = $unpriced;
        $this->pricesFetching = false;
    }

    // -------------------------------------------------------------------------
    // Step 2 → 3
    // -------------------------------------------------------------------------

    public function proceedToAddress(): void
    {
        if ($this->countUnpricedQuotationItems() > 0) {
            $this->dispatch('toast', message: __('app.unpriced_items_block_checkout'), type: 'error');
            return;
        }

        $this->currentStep = 3;
    }

    // -------------------------------------------------------------------------
    // Step 3 → 4: place order
    // -------------------------------------------------------------------------

    /** Validate address and close the picker (dispatches event to Alpine). */
    public function saveAddress(): void
    {
        if ($this->deliveryAddressMode === 'national') {
            $this->validate([
                'deliveryShortAddress' => ['required', 'regex:/^[A-Za-z0-9]{8}$/'],
            ], [
                'deliveryShortAddress.required' => 'العنوان القصير مطلوب.',
                'deliveryShortAddress.regex'    => 'العنوان القصير يجب أن يكون 8 خانات (حروف وأرقام إنجليزية).',
            ]);
        } else {
            $this->validate([
                'deliveryBuildingNo'   => 'nullable|regex:/^\d{0,4}$/',
                'deliveryStreet'       => 'required|string|max:200',
                'deliveryDistrict'     => 'required|string|max:100',
                'deliveryCity'         => 'required|string|max:100',
                'deliveryRegion'       => 'required|string|max:100',
                'deliveryPostalCode'   => ['required', 'regex:/^\d{5}$/'],
                'deliveryAdditionalNo' => 'nullable|regex:/^\d{0,4}$/',
            ], [
                'deliveryStreet.required'     => 'اسم الشارع مطلوب.',
                'deliveryDistrict.required'   => 'الحي مطلوب.',
                'deliveryCity.required'       => 'المدينة مطلوبة.',
                'deliveryRegion.required'     => 'المنطقة مطلوبة.',
                'deliveryPostalCode.required' => 'الرمز البريدي مطلوب.',
                'deliveryPostalCode.regex'    => 'الرمز البريدي يجب أن يكون 5 أرقام.',
            ]);
        }

        $this->dispatch('address-saved');
    }

    public function placeOrder(): void
    {
        if ($this->deliveryAddressMode === 'national') {
            $this->validate([
                'deliveryShortAddress' => ['required', 'regex:/^[A-Za-z0-9]{8}$/'],
                'paymentMethod'        => 'required|string|in:bank_transfer,cash,credit',
            ]);
        } else {
            $this->validate([
                'deliveryStreet'     => 'required|string|max:200',
                'deliveryDistrict'   => 'required|string|max:100',
                'deliveryCity'       => 'required|string|max:100',
                'deliveryRegion'     => 'required|string|max:100',
                'deliveryPostalCode' => ['required', 'regex:/^\d{5}$/'],
                'paymentMethod'      => 'required|string|in:bank_transfer,cash,credit',
            ], [
                'deliveryStreet.required'     => 'اسم الشارع مطلوب.',
                'deliveryDistrict.required'   => 'الحي مطلوب.',
                'deliveryCity.required'       => 'المدينة مطلوبة.',
                'deliveryRegion.required'     => 'المنطقة مطلوبة.',
                'deliveryPostalCode.required' => 'الرمز البريدي مطلوب.',
                'deliveryPostalCode.regex'    => 'الرمز البريدي يجب أن يكون 5 أرقام.',
            ]);
        }

        if (! $this->quotationId) {
            $this->dispatch('toast', message: 'Quotation not found. Please go back and try again.', type: 'error');
            return;
        }

        if ($this->countUnpricedQuotationItems() > 0) {
            $this->dispatch('toast', message: __('app.unpriced_items_block_checkout'), type: 'error');
            return;
        }

        try {
            $order = DB::transaction(function () {
                $quotation = QuotationRequest::findOrFail($this->quotationId);
                if ($this->countUnpricedQuotationItems() > 0) {
                    throw new \RuntimeException('Cannot place order with unpriced quotation items.');
                }

                $version = QuotationVersion::create([
                    'quotation_request_id' => $quotation->id,
                    'version_number'       => 1,
                    'prepared_by'          => Auth::id(),
                    'status'               => QuotationVersionStatusEnum::Accepted,
                    'valid_until'          => now()->addDays(30)->toDateString(),
                    'notes'                => 'Auto-generated from BOQ wizard',
                ]);

                $quotationItems = QuotationItem::where('quotation_request_id', $quotation->id)->get();
                $totalAmount    = 0;

                foreach ($quotationItems as $qi) {
                    $unitPrice  = is_numeric($qi->unit_price) && $qi->unit_price > 0 ? (float) $qi->unit_price : 0;
                    $quantity   = (float) $qi->quantity;
                    $totalPrice = round($unitPrice * $quantity, 2);

                    QuotationVersionItem::create([
                        'quotation_version_id' => $version->id,
                        'quotation_item_id'    => $qi->id,
                        'product_id'           => $qi->product_id,
                        'description'          => (string) $qi->description,
                        'quantity'             => $quantity,
                        'unit_id'              => $qi->unit_id,
                        'unit_price'           => $unitPrice,
                        'total_price'          => $totalPrice,
                        'price_source'         => $qi->price_source ?? 'manual',
                        'vat_rate'             => 15,
                    ]);

                    $totalAmount += $totalPrice;
                }

                $vatAmount  = round($totalAmount * 0.15, 2);
                $grandTotal = round($totalAmount + $vatAmount, 2);

                $order = Order::create([
                    'order_no'               => $this->generateOrderNo(),
                    'quotation_request_id'   => $quotation->id,
                    'quotation_version_id'   => $version->id,
                    'client_id'              => Auth::id(),
                    'project_id'             => $quotation->project_id,
                    'status'                 => OrderStatusEnum::Open,
                    'total_amount'           => $totalAmount,
                    'vat_amount'             => $vatAmount,
                    'grand_total'            => $grandTotal,
                    'currency'               => 'SAR',
                    'delivery_address_type'  => $this->deliveryAddressMode,
                    'delivery_short_address' => $this->deliveryAddressMode === 'national' ? strtoupper($this->deliveryShortAddress) : null,
                    'delivery_building_no'   => $this->deliveryBuildingNo,
                    'delivery_street'        => $this->deliveryStreet,
                    'delivery_district'      => $this->deliveryDistrict,
                    'delivery_city'          => $this->deliveryCity,
                    'delivery_region'        => $this->deliveryRegion,
                    'delivery_postal_code'   => $this->deliveryPostalCode,
                    'delivery_additional_no' => $this->deliveryAdditionalNo,
                    'delivery_country'       => 'SA',
                    'notes'                  => 'Payment: ' . $this->paymentMethod,
                ]);

                foreach ($quotationItems as $qi) {
                    $unitPrice  = is_numeric($qi->unit_price) && $qi->unit_price > 0 ? (float) $qi->unit_price : 0;
                    $quantity   = (float) $qi->quantity;
                    $totalPrice = round($unitPrice * $quantity, 2);

                    OrderItem::create([
                        'order_id'    => $order->id,
                        'product_id'  => $qi->product_id,
                        'description' => (string) $qi->description,
                        'quantity'    => $quantity,
                        'unit_id'     => $qi->unit_id,
                        'unit_price'  => $unitPrice,
                        'total_price' => $totalPrice,
                        'vat_rate'    => 15,
                        'discount_pct' => 0,
                    ]);
                }

                $quotation->update(['status' => QuotationRequestStatusEnum::InReview]);

                return $order;
            });

            $this->orderUuid       = $order->uuid;
            $this->orderNo         = $order->order_no;
            $this->orderGrandTotal = (float) $order->grand_total;
            $this->currentStep     = 4;

            app(NotificationService::class)->sendToUserAndAdmins(
                title: 'طلب جديد',
                body: 'تم إنشاء طلب جديد رقم ' . $this->orderNo . ' من مشروع "' . ($this->boq->project?->name ?? '') . '".',
                type: NotificationTypeEnum::BoqSubmitted,
                userId: Auth::id(),
                actionUrl: route('enduser.orders.show', $this->orderUuid),
            );

        } catch (\Throwable $e) {
            Log::error('ShowBoq::placeOrder failed.', ['message' => $e->getMessage()]);
            $this->dispatch('toast', message: 'فشل إنشاء الطلب. يرجى المحاولة مرة أخرى.', type: 'error');
        }
    }

    public function goBack(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    // -------------------------------------------------------------------------
    // Render
    // -------------------------------------------------------------------------

    public function render()
    {
        $selectedCount = collect($this->items)->filter(fn($i) => $i['selected'] ?? false)->count();

        return view('livewire.enduser.boqs.show-boq', [
            'selectedCount' => $selectedCount,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function countUnpricedQuotationItems(): int
    {
        if (! $this->quotationId) {
            return $this->unpricedCount;
        }

        return QuotationItem::where('quotation_request_id', $this->quotationId)
            ->where('is_selected', true)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) {
                $query->whereNull('unit_price')
                    ->orWhere('unit_price', '<=', 0);
            })
            ->count();
    }

    private function loadItems(): void
    {
        $this->items = $this->boq
            ->items()
            ->where('status', '!=', 'rejected')
            ->with('product')
            ->get()
            ->map(fn(BoqItem $item) => [
                'id'                   => $item->id,
                'description'          => (string) $item->description,
                'quantity'             => (float) $item->quantity,
                'unit_price'           => is_numeric($item->unit_price) ? (float) $item->unit_price : null,
                'unit'                 => $item->unit?->name ?? '',
                'unit_id'              => $item->unit_id,
                'product_id'           => $item->product_id,
                'category'             => (string) ($item->category ?? ''),
                'brand'                => (string) ($item->brand ?? ''),
                'status'               => $item->status->value ?? 'pending',
                'engineering_required' => (bool) $item->engineering_required,
                'confidence'           => $item->confidence,
                'ai_extracted'         => (bool) $item->ai_extracted,
                'selected'             => (bool) $item->is_selected,
            ])
            ->toArray();
    }

    private function generateQuotationNo(): string
    {
        $prefix = 'QR-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (QuotationRequest::where('quotation_no', $candidate)->exists());

        return $candidate;
    }

    private function generateOrderNo(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(4));
        } while (Order::where('order_no', $candidate)->exists());

        return $candidate;
    }

    private function resolveUnitId(?int $unitId, mixed $unitText): ?int
    {
        if ($unitId !== null) {
            return $unitId;
        }

        $label = trim((string) ($unitText ?? ''));
        if ($label === '') {
            return null;
        }

        return Unit::firstOrCreate(
            ['name' => $label],
            ['symbol' => mb_strtolower(mb_substr($label, 0, 20))]
        )->id;
    }
}
