<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model {
    use HasFactory;
    protected $fillable = ['title','slug','starts_at','ends_at','location','category',
        'description','cover_image','is_published'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_published' => 'boolean'];
    public function scopePublished($q) { return $q->where('is_published', true); }
    public function scopeUpcoming($q) { return $q->whereDate('starts_at', '>=', today())->orderBy('starts_at'); }
    public function getRouteKeyName(): string { return 'slug'; }
}
