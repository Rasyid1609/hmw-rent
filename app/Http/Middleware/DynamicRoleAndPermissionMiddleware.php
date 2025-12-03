<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\RouteAccess;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

class DynamicRoleAndPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       $routeName = Route::currentRouteName();
        $routeAccesses = RouteAccess::query()->where('route_name', $routeName)->get();

        if ($routeAccesses->isNotEmpty()) {
            $isAuthorized = false;

            foreach($routeAccesses as $routeAccess) {
                $role = $routeAccess->role_id ? Role::find($routeAccess->role_id) : null;
                $permission = $routeAccess->permission_id ? Permission::find($routeAccess->permission_id) : null;

                if (($role && auth()->user()->hasRole($role->name)) || ($permission && auth()->user()->can($permission->name))){
                    $isAuthorized = true;
                    break;
                }
            }

            if ($isAuthorized){
                return $next($request);
            } else {
                throw UnauthorizedException::forRolesOrPermissions(
                    $routeAccesses->pluck('role_id')->filter()->map(function($role_id){
                        return Role::find($role_id)->name;
                    })->all(),
                    $routeAccesses->pluck('permission_id')->filter()->map(function($permissionId){
                        return Role::find($permission_id)->name;
                    })->all(),
                );
            }
        } else {
            throw UnauthorizedException::forRolesOrPermissions([], []);
        }

        return $next($request);
    }
}
