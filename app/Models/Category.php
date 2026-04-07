<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  protected $fillable = ['marketplace_id', 'parent_id', 'name', 'slug', 'icon', 'sort_order'];

    public function marketplace() { return $this->belongsTo(Marketplace::class); }
    public function parent()      { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children()    { return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order'); }
    public function ads()         { return $this->hasMany(Ad::class); }
}
