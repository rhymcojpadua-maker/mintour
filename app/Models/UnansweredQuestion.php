<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnansweredQuestion extends Model {
    protected $fillable = ['chatbot_session_id','user_id','question','lang','is_resolved',
        'resolved_knowledge_article_id','resolved_at'];
    protected $casts = ['is_resolved' => 'boolean', 'resolved_at' => 'datetime'];

    public function session() { return $this->belongsTo(ChatbotSession::class, 'chatbot_session_id'); }
    public function resolvedArticle() { return $this->belongsTo(KnowledgeArticle::class, 'resolved_knowledge_article_id'); }
    public function scopeOpen($q) { return $q->where('is_resolved', false); }
}
