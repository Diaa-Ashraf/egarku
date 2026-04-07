<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdImage extends Model
{
       protected $fillable = ['ad_id', 'path', 'is_main', 'sort_order'];
    protected $casts    = ['is_main' => 'boolean'];

    public function ad() { return $this->belongsTo(Ad::class); }
}
