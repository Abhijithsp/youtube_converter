<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = [
        'youtube_playlist_id',
        'title',
        'total_videos',
        'processed_videos',
        'status',
    ];

    protected $casts = [
        'total_videos' => 'integer',
        'processed_videos' => 'integer',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(PlaylistItem::class);
    }

    public function selectedItems()
    {
        return $this->hasMany(PlaylistItem::class)->where('selected', true);
    }

    // Helper methods
    public function incrementProcessed()
    {
        $this->increment('processed_videos');
        
        if ($this->processed_videos >= $this->total_videos) {
            $this->update(['status' => 'completed']);
        }
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_videos == 0) {
            return 0;
        }
        
        return round(($this->processed_videos / $this->total_videos) * 100);
    }
}
