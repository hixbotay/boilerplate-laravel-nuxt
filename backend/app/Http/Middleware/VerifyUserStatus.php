<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $status = (int) $user->status; // 0: pending, 1: active, 2: inactive
            $requestPath =  $request->path();
            $paths = ['auth', 'send-otp', 'send-otp/email', 'send-otp/mobile', 'verify'];

            if ($status === 2) return response()->json(["message" => "Please active your account"], 403);
            
            if (in_array($requestPath, $paths) || $status === 1) return $next($request);
        }

        return response()->json(["message" => "Unauthorized"], 401);
    }
}
