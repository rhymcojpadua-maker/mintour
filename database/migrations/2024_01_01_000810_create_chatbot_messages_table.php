<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chatbot_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chatbot_session_id')->constrained()->cascadeOnDelete();
            $t->enum('role', ['user','assistant','system']);
            $t->text('content');
            $t->string('lang', 5)->nullable();
            $t->json('retrieved_kb_ids')->nullable();
            $t->unsignedInteger('tokens_used')->nullable();
            $t->unsignedInteger('latency_ms')->nullable();
            $t->string('feedback', 12)->nullable();  // helpful | not_helpful | null
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('chatbot_messages'); }
};
