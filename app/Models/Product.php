<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model {
    use HasFactory;
    protected $fillable = ['name','slug','maker','price','description','cover_image','is_published'];
    protected $casts = ['price' => 'float', 'is_published' => 'boolean'];
    public function scopePublished($q) { return $q->where('is_published', true); }
    public function getRouteKeyName(): string { return 'slug'; }
}
