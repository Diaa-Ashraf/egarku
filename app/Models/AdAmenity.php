<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AdAmenity extends Model
{
    protected $table = 'ad_amenity';

    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'amenity_id',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
