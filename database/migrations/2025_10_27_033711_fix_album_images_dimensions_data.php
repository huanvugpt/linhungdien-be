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
        // Fix any album images that have dimensions stored as double-encoded JSON
        \DB::table('album_images')->whereNotNull('dimensions')->orderBy('id')->chunk(100, function ($images) {
            foreach ($images as $image) {
                if ($image->dimensions) {
                    $dimensions = $image->dimensions;
                    
                    // Check if it's already a valid JSON array
                    $decoded = json_decode($dimensions, true);
                    
                    // If it's a string that needs to be decoded again, fix it
                    if (is_string($decoded)) {
                        $redecoded = json_decode($decoded, true);
                        if (is_array($redecoded) && isset($redecoded['width']) && isset($redecoded['height'])) {
                            \DB::table('album_images')
                                ->where('id', $image->id)
                                ->update(['dimensions' => json_encode($redecoded)]);
                        }
                    }
                    // If it's not valid JSON at all, set to null
                    elseif (!is_array($decoded)) {
                        \DB::table('album_images')
                            ->where('id', $image->id)
                            ->update(['dimensions' => null]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data fix
    }
};
