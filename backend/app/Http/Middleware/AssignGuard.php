<?php

namespace App\Http\Middleware;

use App\Http\Enums\UserRoleType;
use Illuminate\Support\Facades\Auth;
use Closure;

class AssignGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        
        if (Auth::check()) {
            $user = Auth::user();
            $roles = $user->getRole();
            // Check roles: $user->role is Array
            if ($roles) {
                $permission_name = $request->route()->getName();
                foreach($roles as $role){
                    if ($role->type == UserRoleType::ADMIN['value']) {
                        return $next($request);
                    }
                    // check permission
                    // permission_name is same as route name
                    $user_permission = $role->permission;
                    
                    if (in_array($permission_name, $user_permission)) {
                        return $next($request);
                    }
                }
            }

            return response()->json(["message" => "Permission denied."], 403);
        }

        return response()->json(["message" => "Not Authorized."], 401);
    }
}
