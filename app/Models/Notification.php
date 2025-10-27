<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'data',
        'scheduled_at',
        'sent_at',
        'stopped_at',
        'is_recurring',
        'recurring_pattern',
        'recurring_frequency',
        'recurring_until',
        'status',
        'total_recipients',
        'total_sent',
        'total_read',
        'total_failed',
        'created_by',
        'target_type',
        'target_user_id',
        'target_users',
        'target_ids',
        'target_criteria',
        'priority',
        'action_url',
        'action_text'
    ];

    protected $casts = [
        'data' => 'array',
        'target_criteria' => 'array',
        'target_users' => 'array',
        'target_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'stopped_at' => 'datetime',
        'recurring_until' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    // Notification types
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_SUCCESS = 'success';
    const TYPE_POST_PUBLISHED = 'post_published';
    const TYPE_POST_APPROVED = 'post_approved';
    const TYPE_POST_REJECTED = 'post_rejected';
    const TYPE_SYSTEM_ANNOUNCEMENT = 'system_announcement';
    const TYPE_DAILY_DIGEST = 'daily_digest';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_STOPPED = 'stopped';

    // Target types
    const TARGET_ALL = 'all';
    const TARGET_USERS = 'users';
    const TARGET_ADMINS = 'admins';
    const TARGET_ACTIVE_USERS = 'active_users';
    const TARGET_SPECIFIC_USERS = 'specific_users';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * The creator of this notification (admin)
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Recipients of this notification
     */
    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    /**
     * Get unread recipients
     */
    public function unreadRecipients()
    {
        return $this->recipients()->where('is_read', false);
    }

    /**
     * Get read recipients
     */
    public function readRecipients()
    {
        return $this->recipients()->where('is_read', true);
    }

    /**
     * Check if notification is scheduled for future
     */
    public function isScheduled()
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    /**
     * Check if notification is ready to send
     */
    public function isReadyToSend()
    {
        return $this->status === self::STATUS_SCHEDULED && 
               $this->scheduled_at && 
               $this->scheduled_at->isPast();
    }

    /**
     * Check if notification has been sent
     */
    public function isSent()
    {
        return $this->status === self::STATUS_SENT && $this->sent_at;
    }

    /**
     * Check if notification is recurring
     */
    public function isRecurring()
    {
        return $this->is_recurring && $this->recurring_pattern;
    }

    /**
     * Get next scheduled time for recurring notification
     */
    public function getNextScheduledTime()
    {
        if (!$this->isRecurring()) {
            return null;
        }

        $lastSent = $this->sent_at ?: $this->scheduled_at;
        
        switch ($this->recurring_pattern) {
            case 'daily':
                return $lastSent->addDay();
            case 'weekly':
                return $lastSent->addWeek();
            case 'monthly':
                return $lastSent->addMonth();
            case 'hourly':
                return $lastSent->addHour();
            default:
                return null;
        }
    }

    /**
     * Get target users based on criteria
     */
    public function getTargetUsers()
    {
        // Normalize target_type for backward compatibility
        $targetType = $this->target_type;
        
        \Log::info("getTargetUsers called", [
            'notification_id' => $this->id,
            'target_type' => $targetType
        ]);
        
        // Map different naming conventions to model constants
        $targetTypeMap = [
            'all_users' => self::TARGET_ALL,
            'all' => self::TARGET_ALL,
            'specific_user' => self::TARGET_SPECIFIC_USERS,
            'specific_users' => self::TARGET_SPECIFIC_USERS,
            'user_group' => self::TARGET_USERS,
            'users' => self::TARGET_USERS,
            'new_users' => self::TARGET_USERS,
            'active_users' => self::TARGET_ACTIVE_USERS,
            'admins' => self::TARGET_ADMINS,
        ];
        
        $normalizedType = $targetTypeMap[$targetType] ?? $targetType;
        
        \Log::info("Target type normalized", [
            'original' => $targetType,
            'normalized' => $normalizedType,
            'TARGET_ALL' => self::TARGET_ALL
        ]);
        
        switch ($normalizedType) {
            case self::TARGET_ALL:
                \Log::info("Fetching all users and admins");
                $users = User::all();
                $admins = Admin::all();
                \Log::info("Fetched counts", [
                    'users' => $users->count(),
                    'admins' => $admins->count()
                ]);
                // Use concat instead of merge to preserve all items
                return $users->concat($admins);
                
            case self::TARGET_USERS:
                $query = User::query();
                if ($this->target_criteria) {
                    // Apply additional filters based on criteria
                    if (isset($this->target_criteria['status'])) {
                        $query->where('status', $this->target_criteria['status']);
                    }
                    if (isset($this->target_criteria['created_after'])) {
                        $query->where('created_at', '>=', $this->target_criteria['created_after']);
                    }
                }
                return $query->get();
                
            case self::TARGET_ADMINS:
                return Admin::all();
                
            case self::TARGET_ACTIVE_USERS:
                return User::approved()->where('last_login_at', '>=', now()->subDays(30))->get();
                
            case self::TARGET_SPECIFIC_USERS:
                // Handle both specific_users and specific for backward compatibility
                if (isset($this->target_criteria['user_ids'])) {
                    // If using target_criteria
                    return User::whereIn('id', $this->target_criteria['user_ids'])->get();
                } elseif ($this->target_user_id) {
                    // Single user ID
                    return User::where('id', $this->target_user_id)->get();
                }
                return collect();
                
            default:
                \Log::warning("Unknown target type", ['type' => $normalizedType]);
                return collect();
        }
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'total_sent' => $this->recipients()->count()
        ]);
    }

    /**
     * Update read statistics
     */
    public function updateReadStats()
    {
        $this->update([
            'total_read' => $this->readRecipients()->count()
        ]);
    }

    /**
     * Get notification icon based on type
     */
    public function getIcon()
    {
        return match($this->type) {
            self::TYPE_INFO => 'fas fa-info-circle',
            self::TYPE_WARNING => 'fas fa-exclamation-triangle',
            self::TYPE_ERROR => 'fas fa-times-circle',
            self::TYPE_SUCCESS => 'fas fa-check-circle',
            self::TYPE_POST_PUBLISHED => 'fas fa-newspaper',
            self::TYPE_POST_APPROVED => 'fas fa-thumbs-up',
            self::TYPE_POST_REJECTED => 'fas fa-thumbs-down',
            self::TYPE_SYSTEM_ANNOUNCEMENT => 'fas fa-bullhorn',
            self::TYPE_DAILY_DIGEST => 'fas fa-envelope',
            default => 'fas fa-bell'
        };
    }

    /**
     * Get notification color based on type and priority
     */
    public function getColor()
    {
        // First check priority for urgent/high
        if ($this->priority === 'urgent') {
            return 'danger';
        }
        if ($this->priority === 'high') {
            return 'warning';
        }
        
        // Then check type
        return match($this->type) {
            'info' => 'info',
            'warning' => 'warning',
            'error' => 'danger',
            'success' => 'success',
            'system_announcement' => 'primary',
            'daily_digest' => 'info',
            default => 'info'
        };
    }
    
    /**
     * Get target user if specific user is targeted
     */
    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
    
    /**
     * Get estimated target count
     */
    public function getTargetCount()
    {
        switch ($this->target_type) {
            case 'all_users':
                return User::count();
            case 'specific_user':
                return 1;
            case 'new_users':
                return User::where('created_at', '>=', now()->subDays(30))->count();
            case 'active_users':
                return User::where('updated_at', '>=', now()->subDays(7))->count();
            default:
                return 0;
        }
    }

    /**
     * Scope for pending notifications to send
     */
    public function scopePendingToSend($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope for recurring notifications that need next schedule
     */
    public function scopeNeedingReschedule($query)
    {
        return $query->where('is_recurring', true)
                    ->where('status', self::STATUS_SENT)
                    ->where(function($q) {
                        $q->whereNull('recurring_until')
                          ->orWhere('recurring_until', '>', now());
                    });
    }
}
