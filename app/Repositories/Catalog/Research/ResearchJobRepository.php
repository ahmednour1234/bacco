<?php

namespace App\Repositories\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Models\Catalog\Research\ResearchJob;

class ResearchJobRepository
{
    public function create(array $data): ResearchJob
    {
        return ResearchJob::create($data);
    }

    public function find(int $id): ResearchJob
    {
        return ResearchJob::findOrFail($id);
    }

    public function findByUuid(string $uuid): ResearchJob
    {
        return ResearchJob::where('uuid', $uuid)->firstOrFail();
    }

    public function paginate(int $perPage = 20, array $filters = [])
    {
        return ResearchJob::query()
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['job_type'] ?? null, fn ($q, $t) => $q->where('job_type', $t))
            ->when($filters['product_family_id'] ?? null, fn ($q, $f) => $q->where('product_family_id', $f))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Count jobs for a family that are not in a terminal state. */
    public function activeCountForFamily(int $familyId): int
    {
        return ResearchJob::where('product_family_id', $familyId)
            ->whereNotIn('status', [
                ResearchJobStatusEnum::Completed->value,
                ResearchJobStatusEnum::Failed->value,
                ResearchJobStatusEnum::Cancelled->value,
            ])
            ->count();
    }

    /** Progress % for a family = terminal jobs / all jobs. */
    public function progressForFamily(int $familyId): int
    {
        $total = ResearchJob::where('product_family_id', $familyId)->count();
        if ($total === 0) {
            return 0;
        }

        $done = ResearchJob::where('product_family_id', $familyId)
            ->whereIn('status', [
                ResearchJobStatusEnum::Completed->value,
                ResearchJobStatusEnum::PartiallyCompleted->value,
                ResearchJobStatusEnum::Cancelled->value,
            ])->count();

        return (int) round(($done / $total) * 100);
    }

    /** Cancel every non-terminal job for a family (used by pause/cancel). */
    public function cancelPendingForFamily(int $familyId, ResearchJobStatusEnum $to = ResearchJobStatusEnum::Cancelled): int
    {
        return ResearchJob::where('product_family_id', $familyId)
            ->whereIn('status', [
                ResearchJobStatusEnum::Pending->value,
                ResearchJobStatusEnum::Queued->value,
            ])
            ->update(['status' => $to->value]);
    }

    public function markQueued(ResearchJob $job): void
    {
        $job->update(['status' => ResearchJobStatusEnum::Queued]);
    }

    public function markFailed(ResearchJob $job, string $message): void
    {
        $job->update([
            'status'        => ResearchJobStatusEnum::Failed,
            'failed_at'     => now(),
            'error_message' => $message,
        ]);
    }

    /** Reset a failed job so it can be retried. */
    public function resetForRetry(ResearchJob $job): void
    {
        $job->update([
            'status'        => ResearchJobStatusEnum::Pending,
            'error_message' => null,
            'failed_at'     => null,
        ]);
    }
}
