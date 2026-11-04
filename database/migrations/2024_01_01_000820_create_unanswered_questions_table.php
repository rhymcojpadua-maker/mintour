<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('unanswered_questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chatbot_session_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->text('question');
            $t->string('lang', 5)->default('en');
            $t->boolean('is_resolved')->default(false)->index();
            $t->foreignId('resolved_knowledge_article_id')->nullable()
                ->constrained('knowledge_articles')->nullOnDelete();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('unanswered_questions'); }
};
