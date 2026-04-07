<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class City extends Model
{
      protected $fillable = ['name', 'country', 'is_expat_city'];
    protected $casts    = ['is_expat_city' => 'boolean'];

    public function areas()   { return $this->hasMany(Area::class); }
    public function ads()     { return $this->hasManyThrough(Ad::class, Area::class); }
    public function banners() { return $this->hasMany(Banner::class); }

    public function scopeExpat($q) { return $q->where('is_expat_city', true); }
}
