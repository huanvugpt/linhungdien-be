<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'recipient_type',
        'recipient_id',
        'is_read',
        'read_at',
        'is_dismissed',
        'dismissed_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // User types
    const USER_TYPE_USER = 'user';
    const USER_TYPE_ADMIN = 'admin';

    // Delivery status
    const DELIVERY_PENDING = 'pending';
    const DELIVERY_SENT = 'sent';
    const DELIVERY_DELIVERED = 'delivered';
    const DELIVERY_FAILED = 'failed';

    /**
     * The notification this recipient belongs to
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Get the user (polymorphic relationship)
     */
    public function user()
    {
        if ($this->user_type === self::USER_TYPE_USER) {
            return $this->belongsTo(User::class, 'user_id');
        } else if ($this->user_type === self::USER_TYPE_ADMIN) {
            return $this->belongsTo(Admin::class, 'user_id');
        }
        
        return null;
    }

    /**
     * Get user model based on type
     */
    public function getUser()
    {
        return $this->user_type === self::USER_TYPE_USER 
            ? User::find($this->user_id)
            : Admin::find($this->user_id);
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            // Update notification read statistics
            $this->notification->updateReadStats();
        }
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'delivery_status' => self::DELIVERY_DELIVERED,
            'delivered_at' => now()
        ]);
    }

    /**
     * Mark as failed with error
     */
    public function markAsFailed($error = null)
    {
        $this->update([
            'delivery_status' => self::DELIVERY_FAILED,
            'delivery_error' => $error
        ]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userType, $userId)
    {
        return $query->where('user_type', $userType)
                    ->where('user_id', $userId);
    }

    /**
     * Scope for delivered notifications
     */
    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', self::DELIVERY_DELIVERED);
    }

    /**
     * Scope for failed deliveries
     */
    public function scopeFailed($query)
    {
        return $query->where('delivery_status', self::DELIVERY_FAILED);
    }
}