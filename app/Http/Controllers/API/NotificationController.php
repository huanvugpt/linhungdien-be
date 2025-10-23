<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $notifications = NotificationRecipient::with(['notification'])
                ->where('recipient_id', $user->id)
                ->where('recipient_type', 'user')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'status' => true,
                'data' => [
                    'notifications' => $notifications->items(),
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể tải thông báo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request)
    {
        try {
            $user = Auth::user();
            
            $count = NotificationRecipient::where('recipient_id', $user->id)
                ->where('recipient_type', 'user')
                ->where('is_read', false)
                ->count();

            return response()->json([
                'status' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể tải số thông báo chưa đọc',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            $notificationRecipient = NotificationRecipient::where('id', $id)
                ->where('recipient_id', $user->id)
                ->where('recipient_type', 'user')
                ->first();

            if (!$notificationRecipient) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy thông báo'
                ], 404);
            }

            $notificationRecipient->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Đã đánh dấu đọc thông báo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể đánh dấu đọc thông báo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user();
            
            NotificationRecipient::where('recipient_id', $user->id)
                ->where('recipient_type', 'user')
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Đã đánh dấu đọc tất cả thông báo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể đánh dấu đọc tất cả thông báo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            $notificationRecipient = NotificationRecipient::where('id', $id)
                ->where('recipient_id', $user->id)
                ->where('recipient_type', 'user')
                ->first();

            if (!$notificationRecipient) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy thông báo'
                ], 404);
            }

            $notificationRecipient->delete();

            return response()->json([
                'status' => true,
                'message' => 'Đã xóa thông báo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể xóa thông báo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
