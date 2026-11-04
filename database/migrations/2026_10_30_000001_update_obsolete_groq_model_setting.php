<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'groq_model')
            ->where('value', 'qwen/qwen3.8-27b')
            ->update(['value' => 'openai/gpt-oss-20b']);

        Cache::forget('setting.groq_model');
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'groq_model')
            ->where('value', 'openai/gpt-oss-20b')
            ->update(['value' => 'qwen/qwen3.8-27b']);

        Cache::forget('setting.groq_model');
    }
};
