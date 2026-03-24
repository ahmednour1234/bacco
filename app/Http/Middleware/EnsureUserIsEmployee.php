<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('admin.login');
        }

        $type = $request->user()->user_type;

        if ($type !== UserTypeEnum::Admin && $type !== UserTypeEnum::Employee) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
