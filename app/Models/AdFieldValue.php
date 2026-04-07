<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdFieldValue extends Model
{
      public $timestamps  = false;
    protected $fillable = ['ad_id', 'field_id', 'value'];

    public function ad()    { return $this->belongsTo(Ad::class); }
    public function field() { return $this->belongsTo(MarketplaceField::class, 'field_id'); }
}
