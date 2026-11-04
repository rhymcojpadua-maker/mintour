<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('destinations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('tagline')->nullable();
            $t->text('short_description');
            $t->longText('long_description');
            $t->decimal('latitude', 10, 7)->index();
            $t->decimal('longitude', 10, 7)->index();
            $t->string('address')->nullable();
            $t->string('hours')->nullable();
            $t->string('entrance_fee')->nullable();
            $t->string('best_time')->nullable();
            $t->decimal('distance_km', 6, 2)->nullable();
            $t->decimal('rating_avg', 3, 2)->default(0);
            $t->unsignedInteger('rating_count')->default(0);
            $t->string('cover_image')->nullable();
            $t->boolean('is_featured')->default(false)->index();
            $t->boolean('is_published')->default(true)->index();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();

            $t->index(['category_id','is_published']);
        });
    }
    public function down(): void { Schema::dropIfExists('destinations'); }
};
