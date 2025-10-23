<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripAdminPrefix
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // If response is a redirect and we're on admin subdomain
        if ($response instanceof \Illuminate\Http\RedirectResponse && 
            str_starts_with($request->getHost(), 'admin.')) {
            
            $targetUrl = $response->getTargetUrl();
            
            // Remove /admin prefix from redirect URL
            if (str_contains($targetUrl, '/admin/')) {
                $targetUrl = str_replace('/admin/', '/', $targetUrl);
                $response->setTargetUrl($targetUrl);
            } elseif (str_ends_with($targetUrl, '/admin')) {
                $targetUrl = str_replace('/admin', '/', $targetUrl);
                $response->setTargetUrl($targetUrl);
            }
        }
        
        // If response is HTML and we're on admin subdomain, replace admin URLs in content
        if ($response instanceof \Illuminate\Http\Response && 
            str_starts_with($request->getHost(), 'admin.') &&
            str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            
            $content = $response->getContent();
            
            // Replace admin URLs in HTML, but preserve CSRF tokens and meta tags
            $host = $request->getScheme() . '://' . $request->getHost();
            
            // Use regex to avoid replacing inside value attributes (like CSRF token)
            $content = preg_replace(
                '/(href|action)="\/admin\//i',
                '$1="/',
                $content
            );
            
            // Also handle full URLs
            $content = preg_replace(
                '/(href|action)="' . preg_quote($host, '/') . '\/admin\//i',
                '$1="' . $host . '/',
                $content
            );
            
            // Handle src attributes for assets
            $content = preg_replace(
                '/src="\/admin\/((?!api|storage).+?)"/i',
                'src="/$1"',
                $content
            );
            
            $response->setContent($content);
        }
        
        return $response;
    }
}
