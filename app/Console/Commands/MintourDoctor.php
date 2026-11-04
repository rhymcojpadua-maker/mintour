<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Destination;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\GroqService;
use Illuminate\Console\Command;

class MintourDoctor extends Command
{
    protected $signature = 'mintour:doctor';

    protected $description = 'Verify MinTour install: DB, seed data, storage, and Groq key';

    public function handle(GroqService $groq): int
    {
        $ok = true;

        $this->line("\n<bg=teal;fg=white;options=bold> MinTour installation check </>\n");

        // 1. Database
        try {
            $counts = [
                'Users' => User::count(),
                'Categories' => Category::count(),
                'Destinations' => Destination::count(),
                'KB articles' => KnowledgeArticle::count(),
            ];
            foreach ($counts as $label => $n) {
                $this->line(sprintf('  <fg=green>✔</> %-18s %d', $label.':', $n));
            }
        } catch (\Throwable $e) {
            $this->error('  ✘ Database not reachable: '.$e->getMessage());
            $ok = false;
        }

        // 2. Storage link
        if (file_exists(public_path('storage'))) {
            $this->line('  <fg=green>✔</> Storage link exists');
        } else {
            $this->error('  ✘ Storage link missing — run: php artisan storage:link');
            $ok = false;
        }

        // 3. Groq key
        if ($groq->isConfigured()) {
            $this->line('  <fg=green>✔</> Groq API key configured');
            $this->line('     Model: '.config('groq.model'));
        } else {
            $this->warn('  ⚠ Groq API key not set — chatbot will return fallback messages');
            $this->line('     Create a key at: https://console.groq.com/keys');
        }

        // 4. Admin user
        $admin = User::where('email', env('Mintour_ADMIN_EMAIL', 'admin@mintour.local'))->first();
        if ($admin && $admin->isAdmin()) {
            $this->line('  <fg=green>✔</> Admin user: '.$admin->email);
        } else {
            $this->error('  ✘ Admin user missing — run: php artisan db:seed');
            $ok = false;
        }

        $this->newLine();
        if ($ok) {
            $this->info('  Everything looks good. Visit '.config('app.url'));
        } else {
            $this->error('  Fix the issues above, then re-run php artisan mintour:doctor');
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
