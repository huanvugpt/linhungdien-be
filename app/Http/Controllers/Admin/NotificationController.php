<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Models\Admin;
use App\Jobs\SendNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $query = Notification::with(['creator', 'recipients'])
                            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Search by title or message
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $notifications = $query->paginate(20);

        $stats = [
            'total' => Notification::count(),
            'draft' => Notification::where('status', Notification::STATUS_DRAFT)->count(),
            'scheduled' => Notification::where('status', Notification::STATUS_SCHEDULED)->count(),
            'sent' => Notification::where('status', Notification::STATUS_SENT)->count(),
            'failed' => Notification::where('status', Notification::STATUS_FAILED)->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Show the form for creating a new notification
     */
    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * Store a newly created notification
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,error,success,system_announcement,daily_digest',
            'priority' => 'required|in:normal,high,urgent',
            'target_type' => 'required|in:all_users,specific_user,user_group,new_users,active_users',
            'target_user_id' => 'nullable|exists:users,id|required_if:target_type,specific_user',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string|max:50',
            'scheduled_at' => 'nullable|date|after:now',
            'send_option' => 'required|in:now,scheduled,draft',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|in:daily,weekly,monthly',
            'recurring_until' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Prepare notification data
        $notificationData = [
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'],
            'priority' => $data['priority'] ?? 'normal',
            'target_type' => $data['target_type'],
            'target_user_id' => ($data['target_type'] === 'all_users' || empty($data['target_user_id'])) ? null : $data['target_user_id'],
            'action_url' => $data['action_url'] ?? null,
            'action_text' => $data['action_text'] ?? null,
            'created_by' => Auth::guard('admin')->id(),
            'is_recurring' => $data['is_recurring'] ?? false,
        ];

        // Handle scheduling
        switch ($data['send_option']) {
            case 'now':
                $notificationData['scheduled_at'] = now();
                $notificationData['status'] = 'scheduled';
                break;
            case 'scheduled':
                if (!empty($data['scheduled_at'])) {
                    $notificationData['scheduled_at'] = Carbon::parse($data['scheduled_at']);
                    $notificationData['status'] = 'scheduled';
                } else {
                    $notificationData['status'] = 'draft';
                }
                break;
            case 'draft':
            default:
                $notificationData['status'] = 'draft';
                break;
        }

        // Handle recurring options
        if ($notificationData['is_recurring']) {
            $notificationData['recurring_frequency'] = $data['recurring_frequency'] ?? 'daily';
            if (!empty($data['recurring_until'])) {
                $notificationData['recurring_until'] = Carbon::parse($data['recurring_until']);
            }
        }

        $notification = Notification::create($notificationData);

        // Create recipients based on target type
        $this->createRecipientsForNotification($notification, $data);

        // If scheduled for now, dispatch immediately
        if ($notificationData['status'] === 'scheduled' && 
            isset($notificationData['scheduled_at']) &&
            $notificationData['scheduled_at']->isPast()) {
            SendNotification::dispatch($notification);
        }

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification created successfully!');
    }

    /**
     * Display the specified notification
     */
    public function show(Notification $notification)
    {
        $notification->load(['creator', 'recipients.user']);
        
        $stats = [
            'total_recipients' => $notification->recipients->count(),
            'delivered' => $notification->recipients->where('delivery_status', NotificationRecipient::DELIVERY_DELIVERED)->count(),
            'read' => $notification->recipients->where('is_read', true)->count(),
            'failed' => $notification->recipients->where('delivery_status', NotificationRecipient::DELIVERY_FAILED)->count(),
        ];

        return view('admin.notifications.show', compact('notification', 'stats'));
    }

    /**
     * Send notification immediately
     */
    public function send(Notification $notification)
    {
        if (!in_array($notification->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Only draft or scheduled notifications can be sent.');
        }

        $notification->update([
            'scheduled_at' => now(),
            'status' => 'sending'
        ]);

        SendNotification::dispatch($notification);

        return back()->with('success', 'Notification is being sent!');
    }

    /**
     * Cancel notification
     */
    public function cancel(Notification $notification)
    {
        if ($notification->status === 'sent' || $notification->status === 'sending') {
            return back()->with('error', 'Cannot cancel a notification that has already been sent or is currently being sent.');
        }

        $notification->update(['status' => 'cancelled']);

        return back()->with('success', 'Notification cancelled successfully.');
    }

    /**
     * Stop notification that is currently sending
     */
    public function stop(Notification $notification)
    {
        if ($notification->status !== 'sending') {
            return back()->with('error', 'Only notifications that are currently being sent can be stopped.');
        }

        // Mark notification as stopped
        $notification->update([
            'status' => 'stopped',
            'stopped_at' => now()
        ]);

        // Update any pending recipients to failed status
        $notification->recipients()
            ->where('delivery_status', NotificationRecipient::DELIVERY_PENDING)
            ->update([
                'delivery_status' => NotificationRecipient::DELIVERY_FAILED,
                'updated_at' => now()
            ]);

        // Update total counts
        $totalSent = $notification->recipients()
            ->where('delivery_status', NotificationRecipient::DELIVERY_DELIVERED)
            ->count();
        
        $totalFailed = $notification->recipients()
            ->where('delivery_status', NotificationRecipient::DELIVERY_FAILED)
            ->count();

        $notification->update([
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed
        ]);

        return back()->with('success', 'Notification stopped successfully. Pending recipients marked as failed.');
    }

    /**
     * Show notification statistics
     */
    public function stats()
    {
        $stats = [
            // Basic counts
            'total_notifications' => Notification::count(),
            'total_recipients' => NotificationRecipient::sum('total_recipients'),
            'total_sent' => NotificationRecipient::sum('total_sent'),
            'total_read' => NotificationRecipient::sum('total_read'),
            
            // Status distribution
            'sent_notifications' => Notification::where('status', 'sent')->count(),
            'scheduled_notifications' => Notification::where('status', 'scheduled')->count(),
            'draft_notifications' => Notification::where('status', 'draft')->count(),
            'failed_notifications' => Notification::where('status', 'failed')->count(),
            'cancelled_notifications' => Notification::where('status', 'cancelled')->count(),
            
            // Type distribution
            'by_type' => Notification::selectRaw('type, COUNT(*) as count')
                                   ->groupBy('type')
                                   ->pluck('count', 'type')
                                   ->toArray(),
            
            // Performance metrics
            'success_rate' => 0,
            'failure_rate' => 0,
            'read_rate' => 0,
            'avg_recipients' => 0,
            
            // Time-based stats
            'today' => Notification::whereDate('created_at', today())->count(),
            'this_month' => Notification::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->count(),
            
            // Activity data
            'daily_activity' => [],
            
            // Top performing notifications
            'top_notifications' => Notification::where('status', 'sent')
                                             ->orderBy('total_read', 'desc')
                                             ->limit(10)
                                             ->get(),
            
            // Additional metrics
            'recurring_active' => Notification::where('is_recurring', true)
                                            ->where('status', '!=', 'cancelled')
                                            ->count(),
            'avg_read_time' => null,
            'most_active_hour' => null,
        ];
        
        // Calculate rates
        if ($stats['total_sent'] > 0) {
            $stats['success_rate'] = round(($stats['total_sent'] / $stats['total_recipients']) * 100, 1);
            $stats['failure_rate'] = round(100 - $stats['success_rate'], 1);
            $stats['read_rate'] = round(($stats['total_read'] / $stats['total_sent']) * 100, 1);
        }
        
        if ($stats['total_notifications'] > 0) {
            $stats['avg_recipients'] = round($stats['total_recipients'] / $stats['total_notifications']);
        }
        
        // Generate daily activity for last 30 days
        $dailyActivity = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Notification::whereDate('created_at', $date)->count();
            $dailyActivity[$date->format('M j')] = $count;
        }
        $stats['daily_activity'] = $dailyActivity;

        return view('admin.notifications.stats', compact('stats'));
    }

    /**
     * Remove the specified notification
     */
    public function destroy(Notification $notification)
    {
        if ($notification->status === 'sending') {
            return back()->with('error', 'Cannot delete a notification that is currently being sent.');
        }

        $notification->recipients()->delete();
        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    /**
     * Create recipients for a notification based on target type
     */
    private function createRecipientsForNotification(Notification $notification, array $data)
    {
        $recipients = [];

        switch ($data['target_type']) {
            case 'all_users':
                // Add all active users
                $users = User::where('status', 'active')->get();
                foreach ($users as $user) {
                    $recipients[] = [
                        'notification_id' => $notification->id,
                        'recipient_type' => NotificationRecipient::USER_TYPE_USER,
                        'recipient_id' => $user->id,
                        'is_read' => false,
                        'delivery_status' => NotificationRecipient::DELIVERY_PENDING,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                break;

            case 'specific_user':
                if (!empty($data['target_user_id'])) {
                    $recipients[] = [
                        'notification_id' => $notification->id,
                        'recipient_type' => NotificationRecipient::USER_TYPE_USER,
                        'recipient_id' => $data['target_user_id'],
                        'is_read' => false,
                        'delivery_status' => NotificationRecipient::DELIVERY_PENDING,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                break;

            case 'all_admins':
                // Add all admins
                $admins = Admin::all();
                foreach ($admins as $admin) {
                    $recipients[] = [
                        'notification_id' => $notification->id,
                        'recipient_type' => NotificationRecipient::USER_TYPE_ADMIN,
                        'recipient_id' => $admin->id,
                        'is_read' => false,
                        'delivery_status' => NotificationRecipient::DELIVERY_PENDING,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                break;
        }

        if (!empty($recipients)) {
            NotificationRecipient::insert($recipients);
            
            // Update notification with recipient count
            $notification->update([
                'total_recipients' => count($recipients)
            ]);
        }
    }
}
