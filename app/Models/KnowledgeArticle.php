<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KnowledgeArticle extends Model {
    use HasFactory;

    protected $fillable = ['title','category','content','source','tags','is_published','created_by','updated_by'];
    protected $casts = ['tags' => 'array', 'is_published' => 'boolean'];

    public function scopePublished($q) { return $q->where('is_published', true); }

    /**
     * Full-text weighted search used by KnowledgeRetriever.
     * Returns top N articles ranked by relevance.
     */
    public function scopeSearchRanked($q, string $query, int $limit = 5) {
        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($terms)) return $q->limit(0);

        $like = '%' . implode('%', array_map(fn($t) => addcslashes($t, '%_'), $terms)) . '%';

        return $q->published()
            ->where(function ($sub) use ($like) {
                $sub->where('title', 'like', $like)
                    ->orWhere('content', 'like', $like)
                    ->orWhere('category', 'like', $like);
            })
            ->limit($limit);
    }
}
