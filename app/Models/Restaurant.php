<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model {
    use HasFactory;
    protected $fillable = ['name','slug','cuisine','price_range','rating_avg','address',
        'latitude','longitude','hours','description','cover_image','is_published'];
    protected $casts = ['is_published' => 'boolean', 'rating_avg' => 'float'];
    public function scopePublished($q) { return $q->where('is_published', true); }
    public function getRouteKeyName(): string { return 'slug'; }
}
