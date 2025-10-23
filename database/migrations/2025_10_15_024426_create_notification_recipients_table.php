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
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->string('recipient_type'); // user or admin
            $table->unsignedBigInteger('recipient_id');
            $table->boolean('is_read')->default(false);
            $table->datetime('read_at')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->datetime('dismissed_at')->nullable();
            $table->string('delivery_status')->default('pending'); // pending, delivered, failed
            $table->datetime('delivered_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            
            $table->index(['notification_id', 'recipient_type', 'recipient_id'], 'notif_recip_idx');
            $table->index(['recipient_type', 'recipient_id', 'is_read'], 'recip_read_idx');
            $table->unique(['notification_id', 'recipient_type', 'recipient_id'], 'notif_recip_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
