<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production or when explicitly enabled
        if (config('app.env') === 'production' || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
        
        // Handle HTTPS behind reverse proxy (nginx, cloudflare, etc.)
        if (env('HTTPS_PROXY', false)) {
            $this->app['request']->server->set('HTTPS', 'on');
            URL::forceScheme('https');
        }
        
        // Trust proxy headers for proper IP detection
        if (request()->hasHeader('X-Forwarded-Proto')) {
            request()->server->set('HTTPS', request()->header('X-Forwarded-Proto') === 'https' ? 'on' : 'off');
        }
        
        // Force secure cookies in production
        if (config('app.env') === 'production' || env('SECURE_COOKIES', false)) {
            config(['session.secure' => true]);
            config(['session.same_site' => 'none']);
        }
        
        // Configure default login route
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url('/admin/login').'?token='.$token.'&email='.$user->getEmailForPasswordReset();
        });
    }
}
