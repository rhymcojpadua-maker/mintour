<?php
namespace App\Models;

use App\Enums\ChatRole;
use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model {
    protected $fillable = ['chatbot_session_id','role','content','lang','retrieved_kb_ids',
        'tokens_used','latency_ms','feedback'];
    protected $casts = ['role' => ChatRole::class, 'retrieved_kb_ids' => 'array'];

    public function session() { return $this->belongsTo(ChatbotSession::class, 'chatbot_session_id'); }
}
