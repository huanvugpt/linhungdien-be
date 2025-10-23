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
        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('user_type'); // user or admin
            $table->unsignedBigInteger('user_id');
            $table->boolean('browser_notifications')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->json('notification_types')->nullable(); // Which types of notifications to receive
            $table->boolean('daily_digest')->default(false);
            $table->time('daily_digest_time')->default('09:00:00');
            $table->boolean('weekend_notifications')->default(true);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->timestamps();
            
            $table->unique(['user_type', 'user_id'], 'user_notif_unique');
            $table->index(['user_type', 'user_id', 'browser_notifications'], 'user_notif_browser_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_settings');
    }
};
