<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'pending', // Require admin approval
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Đăng ký thành công! Tài khoản của bạn đang chờ duyệt từ admin.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'status' => $user->status,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Đăng ký thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email hoặc mật khẩu không đúng.'
                ], 401);
            }

            // Check if user is approved
            if (!$user->isApproved()) {
                $message = $user->status === 'pending' 
                    ? 'Tài khoản của bạn đang chờ duyệt từ admin.'
                    : ($user->status === 'rejected' 
                        ? 'Tài khoản của bạn đã bị từ chối.'
                        : 'Tài khoản của bạn đã bị vô hiệu hóa.');
                
                return response()->json([
                    'status' => false,
                    'message' => $message
                ], 403);
            }

            // Create token with expiration
            $expirationMinutes = (int) config('sanctum.expiration', 1440); // Default 24 hours
            $token = $user->createToken('auth-token', ['*'])->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Đăng nhập thành công!',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'phone' => $user->phone,
                        'status' => $user->status,
                        'first_login' => $user->first_login,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => ($expirationMinutes * 60) // minutes to seconds
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Đăng nhập thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'status' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'gender' => $user->gender,
                        'address' => $user->address,
                        'status' => $user->status,
                        'first_login' => $user->first_login,
                        'provider' => $user->provider,
                        'created_at' => $user->created_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get user info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Đăng xuất thành công!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Đăng xuất thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Create new token with expiration
            $expirationMinutes = (int) config('sanctum.expiration', 1440); // Default 24 hours
            $token = $user->createToken('auth-token', ['*'])->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => ($expirationMinutes * 60) // minutes to seconds
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        return $this->me($request);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'address' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $user->update($request->only(['name', 'phone', 'date_of_birth', 'gender', 'address']));

            // Mark profile as completed if it was first login
            if ($user->first_login) {
                $user->update(['first_login' => false]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'date_of_birth' => $user->date_of_birth,
                        'gender' => $user->gender,
                        'address' => $user->address,
                        'avatar' => $user->avatar,
                        'status' => $user->status,
                        'first_login' => $user->first_login,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Delete old avatar if exists
            if ($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))) {
                unlink(storage_path('app/public/' . $user->avatar));
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'status' => true,
                'message' => 'Avatar updated successfully',
                'data' => [
                    'avatar_url' => asset('storage/' . $path)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Avatar update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Revoke all tokens to force re-login
            $user->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Password changed successfully. Please log in again.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Password change failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
