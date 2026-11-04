<?php
namespace App\Models;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model {
    use HasFactory;
    protected $fillable = ['title','body','priority','is_active','published_at'];
    protected $casts = ['priority' => Priority::class, 'is_active' => 'boolean', 'published_at' => 'datetime'];
    public function scopeActive($q) { return $q->where('is_active', true); }
}
