<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chatbot_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('guest_token', 64)->nullable()->index();
            $t->string('lang', 5)->default('en');
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->unsignedInteger('message_count')->default(0);
            $t->timestamp('last_active_at')->nullable()->index();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('chatbot_sessions'); }
};
