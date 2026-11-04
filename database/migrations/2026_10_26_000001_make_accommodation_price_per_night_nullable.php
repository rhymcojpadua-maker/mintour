<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->decimal('price_per_night', 10, 2)->nullable()->change();
        });

        DB::table('accommodations')
            ->where('price_per_night', 0)
            ->update(['price_per_night' => null]);
    }

    public function down(): void
    {
        DB::table('accommodations')
            ->whereNull('price_per_night')
            ->update(['price_per_night' => 0]);

        Schema::table('accommodations', function (Blueprint $table) {
            $table->decimal('price_per_night', 10, 2)->nullable(false)->change();
        });
    }
};
