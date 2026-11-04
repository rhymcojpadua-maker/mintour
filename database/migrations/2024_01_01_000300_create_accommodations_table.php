<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accommodations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('type');                        // Resort, Inn, Homestay, Eco-Lodge
            $t->decimal('price_per_night', 10, 2);
            $t->decimal('rating_avg', 3, 2)->default(0);
            $t->string('address')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->string('contact')->nullable();
            $t->text('description');
            $t->json('amenities')->nullable();
            $t->string('cover_image')->nullable();
            $t->boolean('is_published')->default(true)->index();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('accommodations'); }
};
