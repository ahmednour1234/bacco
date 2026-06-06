<?php

namespace App\Console\Commands;

use App\Models\Boq;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanGuestBoqs extends Command
{
    protected $signature   = 'boq:cleanup-guests {--hours=48 : Delete guest BOQs older than this many hours}';
    protected $description = 'Delete unclaimed guest BOQs (and their projects) that are older than the given threshold.';

    public function handle(): int
    {
        $hours     = (int) $this->option('hours');
        $threshold = now()->subHours($hours);

        $this->info("Cleaning guest BOQs older than {$hours} hours (before {$threshold})...");

        $expiredBoqs = Boq::whereNull('client_id')
            ->whereNotNull('guest_token')
            ->where('created_at', '<', $threshold)
            ->with('uploadedDocuments')
            ->get();

        if ($expiredBoqs->isEmpty()) {
            $this->info('No expired guest BOQs found.');
            return self::SUCCESS;
        }

        $projectIds = $expiredBoqs->pluck('project_id')->filter()->unique()->values();

        foreach ($expiredBoqs as $boq) {
            // Delete uploaded files from disk
            foreach ($boq->uploadedDocuments as $doc) {
                if ($doc->file_path && Storage::disk('local')->exists($doc->file_path)) {
                    Storage::disk('local')->delete($doc->file_path);
                }
            }

            $boq->forceDelete();
        }

        $this->line("  ✓ Deleted {$expiredBoqs->count()} guest BOQ(s).");

        // Clean up any orphaned guest projects (no BOQs left)
        $deletedProjects = 0;
        foreach ($projectIds as $projectId) {
            $project = Project::where('id', $projectId)
                ->where('is_guest', true)
                ->whereNull('client_id')
                ->withCount('boqs')
                ->first();

            if ($project && $project->boqs_count === 0) {
                $project->forceDelete();
                $deletedProjects++;
            }
        }

        if ($deletedProjects > 0) {
            $this->line("  ✓ Deleted {$deletedProjects} orphaned guest project(s).");
        }

        $this->info('Guest BOQ cleanup completed.');

        return self::SUCCESS;
    }
}
