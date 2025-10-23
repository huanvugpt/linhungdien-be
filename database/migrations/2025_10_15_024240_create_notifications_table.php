<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, success, warning, error
            $table->string('status')->default('draft'); // draft, scheduled, sending, sent, failed, cancelled
            $table->string('priority')->default('normal'); // normal, high, urgent
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->json('data')->nullable(); // Additional data for notification
            
            // Target configuration
            $table->string('target_type')->default('all'); // all, users, admins, specific
            $table->json('target_criteria')->nullable(); // Specific user IDs or criteria if target_type is 'specific'
            $table->unsignedBigInteger('target_user_id')->nullable();
            
            // Scheduling & Recurring
            $table->datetime('scheduled_at')->nullable(); // For scheduled notifications
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable(); // daily, weekly, monthly
            $table->string('recurring_pattern')->nullable(); // For backward compatibility
            $table->date('recurring_until')->nullable();
            
            // Statistics
            $table->datetime('sent_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('total_sent')->default(0);
            $table->integer('total_read')->default(0);
            $table->integer('total_failed')->default(0);
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index(['target_type']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
