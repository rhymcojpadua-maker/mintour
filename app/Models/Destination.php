<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Destination extends Model {
    use HasFactory;

    protected $fillable = [
        'category_id','name','slug','tagline','short_description','long_description',
        'latitude','longitude','address','hours','entrance_fee','best_time','distance_km',
        'rating_avg','rating_count','cover_image','is_featured','is_published','sort_order',
    ];
    protected $casts = [
        'latitude' => 'float', 'longitude' => 'float',
        'rating_avg' => 'float', 'distance_km' => 'float',
        'is_featured' => 'boolean', 'is_published' => 'boolean',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function photos()   { return $this->hasMany(DestinationPhoto::class)->orderBy('sort_order'); }
    public function reviews()  { return $this->hasMany(Review::class)->where('is_approved', true); }
    public function favorites(){ return $this->hasMany(Favorite::class); }

    public function scopePublished($q) { return $q->where('is_published', true); }
    public function scopeFeatured($q)  { return $q->where('is_featured', true); }

    public function getRouteKeyName(): string { return 'slug'; }

    // Recalculate rating after review changes
    public function recalcRating(): void {
        $stats = $this->reviews()->selectRaw('AVG(rating) as avg, COUNT(*) as count')->first();
        $this->update([
            'rating_avg'   => round($stats->avg ?? 0, 2),
            'rating_count' => $stats->count ?? 0,
        ]);
    }

    // Haversine: nearby destinations ordered by distance
    public function nearby(int $limit = 3)
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->selectRaw('*, (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance_from_here',
                [$this->latitude, $this->longitude, $this->latitude])
            ->orderBy('distance_from_here')
            ->limit($limit)
            ->get();
    }
}
