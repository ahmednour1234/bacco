<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;

/**
 * Provider call log. Payloads are scrubbed of secrets before saving — this
 * model never persists API keys or Authorization headers.
 */
class AiProviderLog extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'ai_provider_logs';

    public $timestamps = false;

    protected $fillable = [
        'provider', 'model', 'endpoint', 'request_id', 'request_payload',
        'response_status', 'response_payload', 'prompt_tokens',
        'completion_tokens', 'total_tokens', 'duration_ms', 'error_message',
        'created_at',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'created_at'       => 'datetime',
    ];
}
