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
        Schema::create('album_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('album_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('filename'); // Tên file gốc
            $table->string('path'); // Đường dẫn lưu file
            $table->string('mime_type'); // image/jpeg, image/png, etc.
            $table->unsignedInteger('file_size'); // Size file in bytes
            $table->json('dimensions')->nullable(); // {width: 1920, height: 1080}
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false); // Ảnh nổi bật
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            
            $table->index(['album_id', 'sort_order']);
            $table->index(['album_id', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('album_images');
    }
};
