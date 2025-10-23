<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get current user profile
     */
    public function show()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at->format('d/m/Y H:i'),
                'updated_at' => $user->updated_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Update user profile (name, email)
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = Auth::user();
            
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thông tin cá nhân đã được cập nhật thành công!',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'updated_at' => $user->fresh()->updated_at->format('d/m/Y H:i'),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thông tin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = Auth::user();
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không chính xác!',
                    'errors' => [
                        'current_password' => ['Mật khẩu hiện tại không chính xác']
                    ]
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mật khẩu đã được thay đổi thành công!',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đổi mật khẩu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE'
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu để xác nhận',
            'confirmation.required' => 'Vui lòng gõ DELETE để xác nhận',
            'confirmation.in' => 'Vui lòng gõ chính xác DELETE để xác nhận'
        ]);

        try {
            $user = Auth::user();
            
            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu không chính xác!',
                    'errors' => [
                        'password' => ['Mật khẩu không chính xác']
                    ]
                ], 422);
            }

            // Revoke all tokens
            $user->tokens()->delete();
            
            // Delete user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tài khoản đã được xóa thành công!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa tài khoản: ' . $e->getMessage()
            ], 500);
        }
    }
}