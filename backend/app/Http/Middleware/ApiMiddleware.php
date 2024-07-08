<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // ignore path to get assets: images, pdf, ...
		if (strpos($request->path(), 'files') === 0) {
            return $next($request);
        }
		
        
        return $next($request)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            //->header('Access-Control-Allow-Headers', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
	
	public function terminate($request, $response)
	{
		
		// Log::debug('app.requests', ['request' => $request, 'response' => $response]);
	}
}
