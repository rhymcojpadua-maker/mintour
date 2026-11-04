<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accommodation extends Model {
    use HasFactory;
    protected $fillable = ['name','slug','type','price_per_night','rating_avg','address',
        'latitude','longitude','contact','description','amenities','cover_image','is_published'];
    protected $casts = ['amenities' => 'array', 'is_published' => 'boolean', 'price_per_night' => 'float'];
    public function scopePublished($q) { return $q->where('is_published', true); }
    public function getRouteKeyName(): string { return 'slug'; }
}
