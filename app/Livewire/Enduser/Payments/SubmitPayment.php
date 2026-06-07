<?php

namespace App\Livewire\Enduser\Payments;

use App\Enums\NotificationTypeEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\UploadedDocument;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitPayment extends Component
{
    use WithFileUploads;

    public Order $order;

    // form fields
    public string $referenceNumber = '';
    public ?string $notes = null;
    public $receiptFile = null;

    public bool $submitted = false;
    public ?Payment $latestPayment = null;

    protected function rules(): array
    {
        return [
            'referenceNumber' => ['required', 'string', 'min:4', 'max:100'],
            'receiptFile'     => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        $ar = app()->getLocale() === 'ar';
        return [
            'referenceNumber.required' => $ar ? 'رقم المرجع مطلوب' : 'Reference number is required',
            'referenceNumber.min'      => $ar ? 'رقم المرجع قصير جداً' : 'Reference number is too short',
            'receiptFile.required'     => $ar ? 'يرجى رفع صورة الإيصال' : 'Please upload the receipt',
            'receiptFile.mimes'        => $ar ? 'الملف يجب أن يكون صورة أو PDF' : 'File must be an image or PDF',
            'receiptFile.max'          => $ar ? 'الحجم الأقصى 10 ميجابايت' : 'Max file size is 10 MB',
        ];
    }

    public function mount(Order $order): void
    {
        $this->order = $order;
        $this->loadLatestPayment();
    }

    public function loadLatestPayment(): void
    {
        $this->latestPayment = Payment::with('uploadedDocuments')
            ->where('order_id', $this->order->id)
            ->where('client_id', Auth::id())
            ->latest()
            ->first();

        $this->submitted = $this->latestPayment !== null;
    }

    public function submit(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Store receipt file
            $path = $this->receiptFile->store('payments/receipts', 'public');

            // Create payment record
            $payment = Payment::create([
                'order_id'         => $this->order->id,
                'client_id'        => Auth::id(),
                'amount'           => $this->order->grand_total,
                'currency'         => $this->order->currency ?? 'SAR',
                'payment_method'   => 'bank_transfer',
                'status'           => PaymentStatusEnum::Submitted->value,
                'reference_number' => $this->referenceNumber,
                'notes'            => $this->notes,
                'paid_at'          => now(),
            ]);

            // Attach receipt as uploaded document
            UploadedDocument::create([
                'payment_id'   => $payment->id,
                'order_id'     => $this->order->id,
                'uploaded_by'  => Auth::id(),
                'file_name'    => $this->receiptFile->getClientOriginalName(),
                'file_path'    => $path,
                'file_type'    => $this->receiptFile->getMimeType(),
                'file_size'    => $this->receiptFile->getSize(),
            ]);

            $this->latestPayment = $payment->load('uploadedDocuments');
            $this->submitted = true;

            // Notify all admins
            $actionUrl = route('admin.payments.index');
            app(NotificationService::class)->sendToUserType(
                title: app()->getLocale() === 'ar'
                    ? 'تحويل بنكي جديد بانتظار التأكيد'
                    : 'New bank transfer pending confirmation',
                body: app()->getLocale() === 'ar'
                    ? "العميل " . Auth::user()->name . " أرسل إيصال تحويل للطلب رقم {$this->order->order_no}"
                    : "Client " . Auth::user()->name . " submitted a transfer receipt for order {$this->order->order_no}",
                type: NotificationTypeEnum::PaymentSubmitted,
                userType: UserTypeEnum::Employee,
                actionUrl: $actionUrl,
            );
        });

        $this->dispatch('toast', message: app()->getLocale() === 'ar'
            ? 'تم إرسال إيصال الدفع بنجاح، سيتم مراجعته قريباً'
            : 'Payment receipt submitted successfully. It will be reviewed shortly.', type: 'success');
    }

    public function render()
    {
        return view('livewire.enduser.payments.submit-payment');
    }
}
