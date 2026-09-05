<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Models\Role;

class AssignDefaultRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user) {
            if ($user->department === 'Management' || str_contains($user->email, 'admin')) {
                Role::findOrCreate('admin', 'web');
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin');
                }
            } else {
                Role::findOrCreate('employee', 'web');
                if (!$user->hasRole('employee')) {
                    $user->assignRole('employee');
                }
            }
        }

        return $next($request);
    }
}
