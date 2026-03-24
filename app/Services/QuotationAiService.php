<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuotationAiService
{
    private string $baseUrl;

    private string $parseEndpoint;

    private string $apiKey;

    private int $timeout;

    public function __construct()
    {
        $this->baseUrl       = rtrim((string) config('services.ai_quotation.base_url', ''), '/');
        $this->parseEndpoint = ltrim((string) config('services.ai_quotation.parse_endpoint', 'parse'), '/');
        $this->apiKey        = (string) config('services.ai_quotation.api_key', '');
        $this->timeout       = (int) config('services.ai_quotation.timeout', 120);
    }

    /**
     * Send a BOQ file to the external AI endpoint and return extracted items.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $file  Uploaded file or stored path
     * @param  array<string, mixed>  $context  Extra context (quotation_id, project_name, project_status)
     * @return array{success: bool, items: array<int, array<string, mixed>>, error: string|null}
     */
    public function parseBoq(UploadedFile|string $file, array $context = []): array
    {
        if (empty($this->baseUrl)) {
            Log::warning('QuotationAiService: AI_QUOTATION_BASE_URL is not configured.');

            return $this->failure('AI service is not configured.');
        }

        $url = "{$this->baseUrl}/extract/products";

        try {
            $request = Http::timeout($this->timeout)->asMultipart();

            if ($this->apiKey !== '') {
                $request = $request->withToken($this->apiKey);
            }

            // Attach file
            if ($file instanceof UploadedFile) {
                $request = $request->attach(
                    'file',
                    $file->getContent(),
                    $file->getClientOriginalName()
                );
            } else {
                // $file is an absolute path (passed from uploadBoq via Storage::disk('local')->path())
                // Use it directly — do NOT prepend storage_path() again.
                $absPath  = file_exists($file) ? $file : storage_path('app/' . $file);
                $request  = $request->attach(
                    'file',
                    file_get_contents($absPath),
                    basename($file)
                );
            }

            // Attach context fields
            foreach ($context as $key => $value) {
                $request = $request->attach($key, (string) $value);
            }

            $response = $request->post($url);

        } catch (ConnectionException $e) {
            Log::error('QuotationAiService: Connection timeout or refused.', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);

            return $this->failure('AI service timed out or is unavailable. Please try again later.');
        } catch (\Throwable $e) {
            Log::error('QuotationAiService: Unexpected error calling AI endpoint.', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);

            return $this->failure('An unexpected error occurred while contacting the AI service.');
        }

        if (! $response->successful()) {
            Log::error('QuotationAiService: AI endpoint returned non-2xx response.', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->failure("AI service returned HTTP {$response->status()}.");
        }

        $json = $response->json();

        if (! is_array($json)) {
            Log::error('QuotationAiService: AI response is not valid JSON.', [
                'body' => $response->body(),
            ]);

            return $this->failure('AI service returned an invalid response.');
        }

        if (! ($json['success'] ?? true)) {
            $message = $json['message'] ?? 'AI service reported a failure.';
            Log::warning('QuotationAiService: AI returned success=false.', compact('message'));

            return $this->failure($message);
        }

        $items = data_get($json, 'data.items', []);

        if (! is_array($items)) {
            $items = [];
        }

        if (empty($items)) {
            Log::info('QuotationAiService: AI returned zero items for BOQ.', $context);
        }

        return [
            'success' => true,
            'items'   => array_map([$this, 'normaliseItem'], $items),
            'error'   => null,
        ];
    }

    /**
     * Normalise a single AI item into the shape expected by the Livewire component.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function normaliseItem(array $raw): array
    {
        return [
            'description'          => (string) ($raw['product_name'] ?? ''),
            'quantity'             => is_numeric($raw['quantity'] ?? null) ? (float) $raw['quantity'] : 1,
            'unit'                 => (string) ($raw['unit'] ?? ''),
            'category'             => (string) ($raw['category'] ?? ''),
            'brand'                => (string) ($raw['brand'] ?? ''),
            'status'               => 'pending',
            'engineering_required' => (bool) ($raw['engineering_required'] ?? false),
            'confidence'           => is_numeric($raw['confidence'] ?? null) ? (float) $raw['confidence'] : null,
            'raw_data'             => $raw['raw'] ?? null,
            'ai_extracted'         => true,
        ];
    }

    /**
     * @return array{success: bool, items: array, error: string}
     */
    private function failure(string $message): array
    {
        return ['success' => false, 'items' => [], 'error' => $message];
    }
}
