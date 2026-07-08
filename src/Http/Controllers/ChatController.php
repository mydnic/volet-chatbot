<?php

namespace Mydnic\VoletChatbot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Ai\Streaming\Events\Error;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChatController extends Controller
{
    public function store(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:'.config('volet-chatbot.max_message_length')],
            'conversation_id' => ['nullable', 'string'],
        ]);

        $agent = app(config('volet-chatbot.agent'));
        $participant = (object) ['id' => $request->user()?->id];

        if ($data['conversation_id'] ?? null) {
            $agent->continue($data['conversation_id'], $participant);
        } else {
            $agent->forUser($participant);
        }

        $response = $agent->stream($data['message']);

        return response()->stream(function () use ($response) {
            try {
                foreach ($response as $event) {
                    echo 'data: '.$event."\n\n";
                    ob_flush();
                    flush();
                }
            } catch (Throwable $e) {
                report($e);

                $error = new Error(
                    id: (string) str()->uuid(),
                    type: 'error',
                    message: 'The chatbot is unavailable right now. Please try again.',
                    recoverable: true,
                    timestamp: now()->getTimestampMs(),
                );

                echo 'data: '.$error."\n\n";
            }

            echo 'data: '.json_encode([
                'type' => 'conversation',
                'conversation_id' => $response->conversationId,
            ])."\n\n";

            echo "data: [DONE]\n\n";
        }, headers: [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
