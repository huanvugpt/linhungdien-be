<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect to social provider
     */
    public function redirect($provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid social provider'
            ], 400);
        }

        try {
            $redirectUrl = Socialite::driver($provider)->redirect()->getTargetUrl();

            return response()->json([
                'status' => true,
                'data' => [
                    'redirect_url' => $redirectUrl
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate redirect URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle social provider callback
     */
    public function callback($provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            // Redirect to frontend with error
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=invalid_provider');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();

            // Check if user exists with this social ID
            $existingUser = User::where($provider . '_id', $socialUser->getId())->first();

            if ($existingUser) {
                if (!$existingUser->isApproved()) {
                    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=pending_approval');
                }

                // Create token for existing user with expiration
                $expirationMinutes = config('sanctum.expiration', 1440);
                $token = $existingUser->createToken('auth-token', ['*'], now()->addMinutes($expirationMinutes))->plainTextToken;

                // Redirect to frontend success page with token
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login/success?token=' . $token);
            }

            // Check if user exists with same email
            $existingUserByEmail = User::where('email', $socialUser->getEmail())->first();

            if ($existingUserByEmail) {
                // Link social account to existing user
                $existingUserByEmail->update([
                    $provider . '_id' => $socialUser->getId(),
                    'provider' => $provider,
                ]);

                if (!$existingUserByEmail->isApproved()) {
                    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=pending_approval');
                }

                // Create token for linked user with expiration
                $expirationMinutes = config('sanctum.expiration', 1440);
                $token = $existingUserByEmail->createToken('auth-token', ['*'], now()->addMinutes($expirationMinutes))->plainTextToken;

                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login/success?token=' . $token);
            }

            // Create new user
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // Random password for social users
                'provider' => $provider,
                $provider . '_id' => $socialUser->getId(),
                'status' => 'pending', // Require admin approval
                'avatar' => $socialUser->getAvatar(),
                'first_login' => true,
            ]);

            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?success=registration_pending');

        } catch (\Exception $e) {
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=auth_failed');
        }
    }

    /**
     * Get social auth URL (Alternative API method)
     */
    public function getAuthUrl(Request $request, $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid social provider'
            ], 400);
        }

        try {
            $redirectUrl = Socialite::driver($provider)->redirect()->getTargetUrl();

            return response()->json([
                'status' => true,
                'data' => [
                    'auth_url' => $redirectUrl
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate auth URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
