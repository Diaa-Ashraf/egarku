<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceField extends Model
{
    protected $fillable = [
        'marketplace_id', 'name', 'key', 'type',
        'options', 'is_required', 'is_filterable', 'sort_order',
    ];

    protected $casts = [
        'options'       => 'array',
        'is_required'   => 'boolean',
        'is_filterable' => 'boolean',
    ];

    public function marketplace() { return $this->belongsTo(Marketplace::class); }
}
