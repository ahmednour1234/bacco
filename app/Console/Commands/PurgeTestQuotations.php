<?php

namespace App\Console\Commands;

use App\Models\Boq;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Project;
use App\Models\QuotationRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Soft-deletes quotations created while testing, so the admin figures reflect
 * real demand instead of our own trial runs.
 *
 * Two things make this safe to run against production:
 *
 *   1. It soft-deletes. Every model in the chain uses SoftDeletes, and Eloquent
 *      hides soft-deleted rows from every screen and count, so the numbers drop
 *      without the rows going anywhere.
 *   2. It never touches the FK cascade. A hard DELETE on quotation_requests
 *      cascades into orders -> payments and wipes them for good, ignoring their
 *      soft-delete columns entirely. Anything with an order or a payment behind
 *      it is therefore skipped outright and reported, never deleted silently.
 *
 * Dry run is the default; --force is required to write.
 */
class PurgeTestQuotations extends Command
{
    protected $signature = 'quotations:purge-tests
                            {--email=*      : Email of a test account; repeatable}
                            {--guests       : Also purge guest quotations (client_id IS NULL)}
                            {--before=      : Only rows created before this date (Y-m-d)}
                            {--with-orders  : Include rows that have orders/payments (NOT recommended)}
                            {--force        : Actually write. Without it this is a dry run}';

    protected $description = 'Soft-delete test quotations by account email and/or guest status';

    public function handle(): int
    {
        $emails = array_filter(array_map(
            fn($e) => mb_strtolower(trim((string) $e)),
            (array) $this->option('email')
        ));

        $guests = (bool) $this->option('guests');

        if (empty($emails) && ! $guests) {
            $this->error('Nothing selected. Pass --email=… (repeatable) and/or --guests.');
            $this->line('');
            $this->line('  Example:');
            $this->line('    php artisan quotations:purge-tests \\');
            $this->line('      --email=ahmednour5999@gmail.com --email=ss@gm.com --guests');
            $this->line('');
            $this->line('  That prints a report and changes nothing. Add --force to apply.');

            return self::FAILURE;
        }

        // ── Resolve the accounts ─────────────────────────────────────────
        $userIds = collect();

        if ($emails) {
            $users = User::whereIn(DB::raw('LOWER(email)'), $emails)->get();

            $found   = $users->pluck('email')->map(fn($e) => mb_strtolower($e))->all();
            $missing = array_diff($emails, $found);

            foreach ($users as $u) {
                $this->line(sprintf('  account  %-40s id=%-5d %s', $u->email, $u->id, $u->user_type?->value ?? ''));
            }

            foreach ($missing as $m) {
                $this->warn(sprintf('  account  %-40s NOT FOUND - check the spelling', $m));
            }

            if ($missing && ! $this->option('force')) {
                $this->line('');
                $this->warn('Some emails matched no account. Fix them before using --force,');
                $this->warn('otherwise their quotations will silently survive the purge.');
            }

            $userIds = $users->pluck('id');
            $this->line('');
        }

        // ── Build the target set ─────────────────────────────────────────
        $query = QuotationRequest::query()->where(function ($q) use ($userIds, $guests): void {
            if ($userIds->isNotEmpty()) {
                $q->orWhereIn('client_id', $userIds);
            }
            if ($guests) {
                $q->orWhereNull('client_id');
            }
        });

        if ($before = $this->option('before')) {
            $query->whereDate('created_at', '<', $before);
        }

        $targets = $query->with('client')->orderBy('id')->get();

        if ($targets->isEmpty()) {
            $this->info('Nothing matched. Database unchanged.');

            return self::SUCCESS;
        }

        // ── Separate out anything with money attached ────────────────────
        $orderCounts = Order::whereIn('quotation_request_id', $targets->pluck('id'))
            ->selectRaw('quotation_request_id, COUNT(*) AS c')
            ->groupBy('quotation_request_id')
            ->pluck('c', 'quotation_request_id');

        $paidQuotationIds = Payment::query()
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->whereIn('orders.quotation_request_id', $targets->pluck('id'))
            ->distinct()
            ->pluck('orders.quotation_request_id');

        [$protected, $safe] = $targets->partition(
            fn($q) => isset($orderCounts[$q->id]) || $paidQuotationIds->contains($q->id)
        );

        if ($protected->isNotEmpty() && ! $this->option('with-orders')) {
            $this->warn(sprintf(
                '%d quotation(s) have orders or payments behind them and will be SKIPPED:',
                $protected->count()
            ));

            foreach ($protected as $q) {
                $this->line(sprintf(
                    '  skip   #%-6d %-22s orders=%d %s',
                    $q->id,
                    $q->quotation_no,
                    $orderCounts[$q->id] ?? 0,
                    $paidQuotationIds->contains($q->id) ? 'HAS PAYMENTS' : ''
                ));
            }

            $this->line('');
        }

        $toDelete = $this->option('with-orders') ? $targets : $safe;

        if ($toDelete->isEmpty()) {
            $this->info('Everything matched is protected. Database unchanged.');

            return self::SUCCESS;
        }

        // ── Report ───────────────────────────────────────────────────────
        $this->table(
            ['id', 'quotation_no', 'project', 'status', 'owner', 'created'],
            $toDelete->take(40)->map(fn($q) => [
                $q->id,
                $q->quotation_no,
                mb_strimwidth((string) $q->project_name, 0, 24, '…'),
                $q->status?->value ?? '',
                $q->client?->email ?? '(guest)',
                $q->created_at?->format('Y-m-d'),
            ])->all()
        );

        if ($toDelete->count() > 40) {
            $this->line(sprintf('  … and %d more', $toDelete->count() - 40));
        }

        $boqIds     = $toDelete->pluck('boq_id')->filter()->unique();
        $projectIds = $toDelete->pluck('project_id')->filter()->unique();

        $this->line('');
        $this->info(sprintf(
            'Would soft-delete: %d quotation(s), %d BOQ(s), %d project(s).',
            $toDelete->count(),
            $boqIds->count(),
            $projectIds->count()
        ));

        if (! $this->option('force')) {
            $this->line('');
            $this->comment('DRY RUN - nothing was written. Re-run with --force to apply.');

            return self::SUCCESS;
        }

        if (! $this->confirm(sprintf('Soft-delete %d quotation(s)?', $toDelete->count()), false)) {
            $this->info('Aborted. Database unchanged.');

            return self::SUCCESS;
        }

        // ── Apply ────────────────────────────────────────────────────────
        // Eloquent's delete() is used rather than a mass update so each model
        // stamps deleted_at through its own SoftDeletes handling.
        DB::transaction(function () use ($toDelete, $boqIds, $projectIds): void {
            foreach ($toDelete as $quotation) {
                $quotation->delete();
            }

            // Only drop a BOQ or project when no surviving quotation needs it.
            foreach (Boq::whereIn('id', $boqIds)->get() as $boq) {
                $stillUsed = QuotationRequest::where('boq_id', $boq->id)->exists();
                if (! $stillUsed) {
                    $boq->delete();
                }
            }

            foreach (Project::whereIn('id', $projectIds)->get() as $project) {
                // withTrashed() on orders: a soft-deleted order still owns its
                // project, and dropping the project would orphan it on restore.
                $stillUsed = QuotationRequest::where('project_id', $project->id)->exists()
                    || Order::withTrashed()->where('project_id', $project->id)->exists()
                    || Boq::where('project_id', $project->id)->exists();

                if (! $stillUsed) {
                    $project->delete();
                }
            }
        });

        $this->line('');
        $this->info(sprintf('Done. %d quotation(s) soft-deleted.', $toDelete->count()));
        $this->comment('To undo: set deleted_at back to NULL on those rows.');

        return self::SUCCESS;
    }
}
