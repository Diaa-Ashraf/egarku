<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
     protected $fillable = ['marketplace_id', 'name', 'icon'];

    public function marketplace() { return $this->belongsTo(Marketplace::class); }
    public function ads()         { return $this->belongsToMany(Ad::class, 'ad_amenity'); }
}
