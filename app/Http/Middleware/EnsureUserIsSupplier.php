<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSupplier
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('supplier.login');
        }

        $user = $request->user();

        if ($user->user_type !== UserTypeEnum::Supplier) {
            abort(403, 'Unauthorized.');
        }

        if (! $user->active) {
            auth()->logout();
            return redirect()->route('supplier.login')
                ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
        }

        return $next($request);
    }
}
