<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'notification_type',
        'is_enabled',
        'delivery_method',
        'preferences'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'preferences' => 'array',
    ];

    // User types
    const USER_TYPE_USER = 'user';
    const USER_TYPE_ADMIN = 'admin';

    // Delivery methods
    const DELIVERY_PUSH = 'push';
    const DELIVERY_EMAIL = 'email';
    const DELIVERY_SMS = 'sms';
    const DELIVERY_IN_APP = 'in_app';

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
     * Check if user can receive this notification type
     */
    public static function canReceiveNotification($userType, $userId, $notificationType, $deliveryMethod = self::DELIVERY_PUSH)
    {
        $setting = self::where('user_type', $userType)
                      ->where('user_id', $userId)
                      ->first();

        // If no settings exist, default to enabled
        if (!$setting) {
            return true;
        }

        // Check based on delivery method
        switch ($deliveryMethod) {
            case self::DELIVERY_PUSH:
                return $setting->push_notifications ?? true;
            case self::DELIVERY_EMAIL:
                return $setting->email_notifications ?? true;
            case 'browser':
                return $setting->browser_notifications ?? true;
            default:
                return true;
        }
    }

    /**
     * Get user's notification settings
     */
    public static function getUserSettings($userType, $userId)
    {
        return self::where('user_type', $userType)
                  ->where('user_id', $userId)
                  ->get()
                  ->keyBy(function($item) {
                      return $item->notification_type . '_' . $item->delivery_method;
                  });
    }

    /**
     * Update or create notification setting
     */
    public static function updateSetting($userType, $userId, $notificationType, $deliveryMethod, $isEnabled, $preferences = [])
    {
        return self::updateOrCreate(
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'notification_type' => $notificationType,
                'delivery_method' => $deliveryMethod
            ],
            [
                'is_enabled' => $isEnabled,
                'preferences' => $preferences
            ]
        );
    }

    /**
     * Get default notification settings for a user
     */
    public static function getDefaultSettings()
    {
        return [
            // Post notifications
            Notification::TYPE_POST_PUBLISHED => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => false,
                self::DELIVERY_IN_APP => true
            ],
            Notification::TYPE_POST_APPROVED => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => true,
                self::DELIVERY_IN_APP => true
            ],
            Notification::TYPE_POST_REJECTED => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => true,
                self::DELIVERY_IN_APP => true
            ],
            
            // System notifications
            Notification::TYPE_SYSTEM_ANNOUNCEMENT => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => false,
                self::DELIVERY_IN_APP => true
            ],
            Notification::TYPE_DAILY_DIGEST => [
                self::DELIVERY_PUSH => false,
                self::DELIVERY_EMAIL => true,
                self::DELIVERY_IN_APP => false
            ],
            
            // General notifications
            Notification::TYPE_INFO => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => false,
                self::DELIVERY_IN_APP => true
            ],
            Notification::TYPE_WARNING => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => false,
                self::DELIVERY_IN_APP => true
            ],
            Notification::TYPE_ERROR => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => true,
                self::DELIVERY_IN_APP => true
            ],
            Notification::TYPE_SUCCESS => [
                self::DELIVERY_PUSH => true,
                self::DELIVERY_EMAIL => false,
                self::DELIVERY_IN_APP => true
            ]
        ];
    }

    /**
     * Initialize default settings for a user
     */
    public static function initializeForUser($userType, $userId)
    {
        $defaultSettings = self::getDefaultSettings();
        
        foreach ($defaultSettings as $notificationType => $deliveryMethods) {
            foreach ($deliveryMethods as $deliveryMethod => $isEnabled) {
                self::updateOrCreate(
                    [
                        'user_type' => $userType,
                        'user_id' => $userId,
                        'notification_type' => $notificationType,
                        'delivery_method' => $deliveryMethod
                    ],
                    [
                        'is_enabled' => $isEnabled
                    ]
                );
            }
        }
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
     * Scope for enabled settings
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for specific notification type
     */
    public function scopeForNotificationType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope for specific delivery method
     */
    public function scopeForDeliveryMethod($query, $method)
    {
        return $query->where('delivery_method', $method);
    }
}