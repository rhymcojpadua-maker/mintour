<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ChatbotSession;
use App\Models\Destination;
use App\Models\Event;
use App\Models\KnowledgeArticle;
use App\Models\Review;
use App\Models\UnansweredQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $chatActivity = ChatbotSession::selectRaw('DATE(created_at) as activity_date, count(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');

        $chatActivity = collect(range(6, 0))->mapWithKeys(function ($daysAgo) use ($chatActivity) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [$date => $chatActivity->get($date, 0)];
        });

        return view('admin.dashboard', [
            'stats' => [
                'destinations'   => Destination::count(),
                'businesses'     => \App\Models\Accommodation::count() + \App\Models\Restaurant::count(),
                'events'         => Event::count(),
                'users'          => User::count(),
                'kb_articles'    => KnowledgeArticle::count(),
                'chat_sessions'  => ChatbotSession::count(),
                'reviews'        => Review::count(),
                'unanswered'     => UnansweredQuestion::open()->count(),
            ],
            'popular'      => Destination::orderByDesc('rating_count')->take(5)->get(['name','rating_count']),
            'langUsage'    => ChatbotSession::select('lang', DB::raw('count(*) as c'))->groupBy('lang')->pluck('c', 'lang'),
            'chatActivity' => $chatActivity,
            'topQuestions' => \App\Models\ChatbotMessage::where('role','user')
                                ->select('content', DB::raw('count(*) as c'))
                                ->groupBy('content')->orderByDesc('c')->take(5)->get(),
        ]);
    }
}
