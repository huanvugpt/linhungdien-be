<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotification implements ShouldQueue
{
    use Queueable;

    private Notification $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting to send notification', [
                'notification_id' => $this->notification->id,
                'title' => $this->notification->title
            ]);

            // Update status to sending
            $this->notification->update(['status' => 'sending']);

            // Get recipients
            $recipients = $this->notification->recipients()->get();
            
            $totalSent = 0;
            $totalFailed = 0;

            foreach ($recipients as $recipient) {
                try {
                    // Here you would implement actual notification sending
                    // For now, we'll just mark as delivered
                    $recipient->update([
                        'delivery_status' => NotificationRecipient::DELIVERY_DELIVERED,
                        'delivered_at' => now()
                    ]);
                    
                    $totalSent++;
                    
                    Log::info('Notification sent to recipient', [
                        'notification_id' => $this->notification->id,
                        'recipient_id' => $recipient->id,
                        'recipient_type' => $recipient->recipient_type
                    ]);
                    
                } catch (\Exception $e) {
                    $totalFailed++;
                    
                    $recipient->update([
                        'delivery_status' => NotificationRecipient::DELIVERY_FAILED
                    ]);
                    
                    Log::error('Failed to send notification to recipient', [
                        'notification_id' => $this->notification->id,
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update notification status
            $this->notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed
            ]);

            Log::info('Notification sending completed', [
                'notification_id' => $this->notification->id,
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->notification->update([
                'status' => 'failed'
            ]);

            throw $e;
        }
    }
}
