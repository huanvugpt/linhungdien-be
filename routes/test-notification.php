<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Jobs\SendNotification;

// Simple test notification creation
$admin = Admin::first();
$user = User::first();

if (!$admin) {
    echo "No admin found!\n";
    exit;
}

if (!$user) {
    echo "No user found!\n";
    exit;
}

echo "Creating test notification...\n";

// Create notification
$notification = Notification::create([
    'title' => 'Test Notification - ' . now()->format('H:i:s'),
    'message' => 'This is a test notification to verify the system works.',
    'type' => 'info',
    'priority' => 'normal',
    'target_type' => 'all_users',
    'status' => 'scheduled',
    'scheduled_at' => now(),
    'created_by' => $admin->id,
    'total_recipients' => 0
]);

echo "Notification created with ID: {$notification->id}\n";

// Create recipient
$recipient = NotificationRecipient::create([
    'notification_id' => $notification->id,
    'recipient_type' => 'user',
    'recipient_id' => $user->id,
    'is_read' => false,
    'delivery_status' => 'pending'
]);

echo "Recipient created with ID: {$recipient->id}\n";

// Update recipient count
$notification->update(['total_recipients' => 1]);

// Dispatch job
SendNotification::dispatch($notification);

echo "SendNotification job dispatched!\n";
echo "Check queue worker output for processing...\n";