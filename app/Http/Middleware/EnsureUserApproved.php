<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            if (!$user->isApproved()) {
                auth()->logout();
                return redirect()->route('login')->withErrors([
                    'error' => 'Your account is pending approval or has been rejected.'
                ]);
            }
        }
        
        return $next($request);
    }
}
