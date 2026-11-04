<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model {
    use HasFactory;
    protected $fillable = ['question','answer','category','sort_order','is_published'];
    protected $casts = ['is_published' => 'boolean'];
    public function scopePublished($q) { return $q->where('is_published', true); }
}
