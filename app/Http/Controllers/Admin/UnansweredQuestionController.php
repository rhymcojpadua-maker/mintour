<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnansweredQuestion;

class UnansweredQuestionController extends Controller
{
    public function index()
    {
        return view('admin.unanswered.index', [
            'items' => UnansweredQuestion::with('session')->latest()->paginate(20),
        ]);
    }

    public function resolve(UnansweredQuestion $question)
    {
        $question->update(['is_resolved' => true, 'resolved_at' => now()]);
        return back()->with('status', 'Marked as resolved.');
    }

    public function convert(UnansweredQuestion $question)
    {
        return redirect()->route('admin.knowledge.create', [
            'prefill_title'   => $question->question,
            'from_question'   => $question->id,
        ]);
    }
}
