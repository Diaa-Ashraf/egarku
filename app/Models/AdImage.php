<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdImage extends Model
{
    protected $fillable = ['ad_id', 'path', 'is_main', 'sort_order'];
    protected $casts    = ['is_main' => 'boolean'];
    protected $appends  = ['url'];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    /**
     * تحويل المسار النسبي إلى URL كامل
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
