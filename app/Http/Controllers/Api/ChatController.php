<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    /** POST /api/chat/message */
    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'lang'    => ['nullable', 'in:en,fil'],
        ]);

        try {
            $result = $this->chat->handle($data['message'], $data['lang'] ?? null);

            $hasError = ! empty($result['error']);

            return response()->json([
                'ok'         => ! $hasError,
                'reply'      => $result['reply'],
                'html'       => $result['html'],
                'lang'       => $result['lang'],
                'grounded'   => $result['grounded'],
                'error'      => $result['error'] ?? null,
                'session_id' => $result['session_id'],
                'message_id' => $result['message_id'],
            ], $hasError ? 503 : 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Chat message failed', ['exception' => $e]);

            return response()->json([
                'ok'    => false,
                'error' => 'The assistant is temporarily unavailable. Please try again.',
            ], 500);
        }
    }

    /** POST /api/chat/feedback/{message} */
    public function feedback(Request $request, int $message): JsonResponse
    {
        $data = $request->validate([
            'feedback' => ['required', 'in:helpful,not_helpful'],
        ]);

        $ok = $this->chat->recordFeedback($message, $data['feedback']);

        return response()->json(['ok' => $ok]);
    }

    /** GET /api/chat/history — recent messages for the current session. */
    public function history(Request $request): JsonResponse
    {
        $session = $this->currentSession($request);

        if (! $session) {
            return response()->json(['ok' => true, 'messages' => []]);
        }

        $messages = $session->messages()
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn ($m) => [
                'id'      => $m->id,
                'role'    => $m->role->value,
                'content' => $m->content,
                'lang'    => $m->lang,
                'at'      => $m->created_at->toIso8601String(),
            ]);

        return response()->json(['ok' => true, 'messages' => $messages]);
    }

    private function currentSession(Request $request): ?\App\Models\ChatbotSession
    {
        if ($user = $request->user()) {
            return \App\Models\ChatbotSession::where('user_id', $user->id)->latest('id')->first();
        }

        $token = $request->cookie('mintour_guest');
        if (! $token) return null;

        return \App\Models\ChatbotSession::where('guest_token', $token)->latest('id')->first();
    }
}
