<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    protected $fillable = ['service_type', 'duration_days', 'price', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
