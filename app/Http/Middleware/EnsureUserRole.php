<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roleParams): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $allowed = [];
        foreach ($roleParams as $param) {
            foreach (explode('|', $param) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $allowed[] = UserRole::from($part);
                }
            }
        }

        foreach ($allowed as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        abort(403);
    }
}
