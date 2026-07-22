<?php

namespace App\Models\Catalog\Research;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchJobResult extends Model
{
    protected $connection = 'catalog';
    protected $table      = 'research_job_results';

    protected $fillable = [
        'research_job_id', 'raw_response', 'parsed_response', 'validation_status',
        'validation_errors', 'discovered_count', 'accepted_count',
        'rejected_count', 'duplicate_count',
    ];

    protected $casts = [
        'parsed_response'   => 'array',
        'validation_errors' => 'array',
        'discovered_count'  => 'integer',
        'accepted_count'    => 'integer',
        'rejected_count'    => 'integer',
        'duplicate_count'   => 'integer',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ResearchJob::class, 'research_job_id');
    }
}
