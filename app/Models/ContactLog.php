<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactLog extends Model
{
 protected $fillable = ['ad_id', 'user_id', 'contact_type', 'ip_address'];

    public function ad()   { return $this->belongsTo(Ad::class); }
    public function user() { return $this->belongsTo(User::class); }
}
