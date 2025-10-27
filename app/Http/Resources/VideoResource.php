<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'description' => $this->description,
            'youtube_url' => $this->youtube_url,
            'youtube_id' => $this->youtube_id,
            'thumbnail_url' => $this->thumbnail_url,
            'duration' => $this->duration,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'avatar_url' => $this->user->avatar_url,
                ];
            }),
            
            // YouTube embed URL
            'embed_url' => $this->youtube_id ? "https://www.youtube.com/embed/{$this->youtube_id}" : null,
            
            // Check if current user can edit
            'can_edit' => $this->when(auth()->check(), function () {
                return $this->user_id === auth()->id() || auth()->user()->isAdmin();
            }, false),
        ];
    }
}
