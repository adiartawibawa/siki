<?php

namespace App\Http\Middleware;

use App\Enums\Ability;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->can(Ability::ApiAccess->value)) {
            throw new AuthorizationException('API access is not enabled for this account.');
        }

        return $next($request);
    }
}
