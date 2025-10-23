<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';
    const STATUS_REPLIED = 'replied';
    const STATUS_RESOLVED = 'resolved';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'Mới',
            self::STATUS_READ => 'Đã đọc',
            self::STATUS_REPLIED => 'Đã phản hồi',
            self::STATUS_RESOLVED => 'Đã giải quyết',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Không xác định';
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for new contacts
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): bool
    {
        if ($this->status === self::STATUS_NEW) {
            return $this->update(['status' => self::STATUS_READ]);
        }
        return true;
    }

    /**
     * Mark as replied
     */
    public function markAsReplied($repliedBy = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REPLIED,
            'replied_at' => now(),
            'replied_by' => $repliedBy,
        ]);
    }

    /**
     * Mark as resolved
     */
    public function markAsResolved(): bool
    {
        return $this->update(['status' => self::STATUS_RESOLVED]);
    }
}