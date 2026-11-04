<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model {
    protected $fillable = ['user_id','guest_token','lang','ip_address','user_agent','message_count','last_active_at'];
    protected $casts = ['last_active_at' => 'datetime'];

    public function user()     { return $this->belongsTo(User::class); }
    public function messages() { return $this->hasMany(ChatbotMessage::class); }
    public function unanswered() { return $this->hasMany(UnansweredQuestion::class); }

    public function touchActive(): void {
        $this->update(['last_active_at' => now(), 'message_count' => $this->message_count + 1]);
    }
}
