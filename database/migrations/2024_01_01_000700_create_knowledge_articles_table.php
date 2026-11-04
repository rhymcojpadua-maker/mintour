<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('knowledge_articles', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('category')->index();
            $t->longText('content');
            $t->string('source')->nullable();
            $t->json('tags')->nullable();
            $t->boolean('is_published')->default(false)->index();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            if (Schema::getConnection()->getDriverName() === 'mysql') { $t->fullText(['title','content']); }
        });
    }
    public function down(): void { Schema::dropIfExists('knowledge_articles'); }
};
