<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetAdminDomainUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Laravel to use the admin subdomain as root for all URLs
        // This prevents Laravel from generating /admin/login URLs
        $host = $request->header('host', $request->getHost());
        
        if (str_starts_with($host, 'admin.')) {
            URL::forceRootUrl($request->getScheme() . '://' . $host);
            // Force route generation to ignore /admin prefix for admin subdomain
            config(['app.url' => $request->getScheme() . '://' . $host]);
        }
        
        return $next($request);
    }
}