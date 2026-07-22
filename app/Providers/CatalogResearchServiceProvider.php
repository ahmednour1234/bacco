<?php

namespace App\Providers;

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Services\Catalog\Research\Contracts\ResearchResultPersister;
use App\Services\Catalog\Research\DeepSeek\Contracts\AiResearchProvider;
use App\Services\Catalog\Research\DeepSeek\DeepSeekProvider;
use App\Services\Catalog\Research\DeepSeek\FakeResearchProvider;
use App\Services\Catalog\Research\DefaultResearchResultPersister;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the Product Catalog Research module's permission Gates.
 *
 * This app has no Spatie/permission package — authorization is by user_type,
 * enforced in middleware. This provider layers fine-grained `catalog.*`
 * abilities on top so controllers/blades can call can('catalog.import.view')
 * etc., while keeping the existing Admin-vs-Employee model:
 *   - Admins implicitly pass every ability (Gate::before).
 *   - Employees get the subset listed in config('catalog_research.employee_permissions').
 *   - Everyone else is denied.
 */
class CatalogResearchServiceProvider extends ServiceProvider
{
    /** All abilities exposed by the module. */
    private const ABILITIES = [
        'catalog.import.view', 'catalog.import.create', 'catalog.import.process',
        'catalog.family.view', 'catalog.family.manage',
        'catalog.research.start', 'catalog.research.pause', 'catalog.research.cancel',
        'catalog.research.retry',
        'catalog.product.view', 'catalog.product.manage',
        'catalog.product.verify', 'catalog.product.reject',
        'catalog.review.view', 'catalog.review.resolve',
        'catalog.source.view', 'catalog.source.manage',
        'catalog.export',
    ];

    public function register(): void
    {
        // Bind the swappable AI research provider. DeepSeek in production; the
        // deterministic Fake is used by tests (they override this binding).
        $this->app->bind(AiResearchProvider::class, function ($app) {
            return match (config('catalog_research.provider', 'deepseek')) {
                'fake'  => $app->make(FakeResearchProvider::class),
                default => $app->make(DeepSeekProvider::class),
            };
        });

        // Persistence of research results (Phase 5) — swappable if needed.
        $this->app->bind(ResearchResultPersister::class, DefaultResearchResultPersister::class);
    }

    public function boot(): void
    {
        // Admins bypass every catalog.* check.
        Gate::before(function (?User $user, string $ability) {
            if (! str_starts_with($ability, 'catalog.')) {
                return null; // not ours — let other gates decide
            }

            return $user && $user->user_type === UserTypeEnum::Admin ? true : null;
        });

        $allowed = (array) config('catalog_research.employee_permissions', []);

        foreach (self::ABILITIES as $ability) {
            Gate::define($ability, function (?User $user) use ($ability, $allowed) {
                if (! $user) {
                    return false;
                }
                if ($user->user_type === UserTypeEnum::Admin) {
                    return true;
                }
                if ($user->user_type === UserTypeEnum::Employee) {
                    return in_array($ability, $allowed, true);
                }

                return false;
            });
        }
    }
}
