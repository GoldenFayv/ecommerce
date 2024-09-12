<?php

namespace App\Http\Middleware;

use App\Models\Admin\Admin;
use Closure;
use Illuminate\Http\Request;
use App\Models\admin\Permission;
use Symfony\Component\HttpFoundation\Response;



class RequiresAccess
{

    /**
     * Handle an incoming request.
     * @param string $permission The Permission Code
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        /**
         * @var Admin
         */
        $admin = auth("admin")->user();

        if (!empty(array_filter($permissions, fn($permission) => $admin->canAccess(code: $permission)))) {
            return $next($request);
        } else {
            return response()->json([
                "message" => "Access Denied",
            ], 401);
        }
    }

}

