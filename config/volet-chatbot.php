<?php

use Mydnic\VoletChatbot\Agents\ChatbotAgent;

return [
    // The agent class that answers the widget. Point this at your own class
    // (extending the default one, or implementing Laravel\Ai\Contracts\Agent
    // yourself) to add tools, MCP, structured output, etc. See laravel/ai
    // docs, nothing chatbot-specific is needed for that.
    'agent' => ChatbotAgent::class,

    // Which entry in config('ai.php')'s "providers" array to use. The
    // "openai-compatible" driver accepts any OpenAI-compatible endpoint via
    // OPENAI_COMPATIBLE_URL / OPENAI_COMPATIBLE_API_KEY — including your own
    // Laravel app's backend, or OpenAI directly (use the "openai" provider
    // for that instead).
    'provider' => env('VOLET_CHATBOT_PROVIDER', 'openai-compatible'),

    'model' => env('VOLET_CHATBOT_MODEL', 'gpt-4o-mini'),

    'system_prompt' => env('VOLET_CHATBOT_SYSTEM_PROMPT', 'You are a helpful assistant.'),

    // Maximum characters accepted per user message. A cheap guardrail
    // against someone pasting a novel into a public widget.
    'max_message_length' => 4000,

    'routes' => [
        'prefix' => 'chatbot',

        'middleware' => [
            'web',
            // Rate limit is keyed by authenticated user id, falling back to
            // IP, Laravel's default throttle behavior.
            'throttle:'.env('VOLET_CHATBOT_RATE_LIMIT', '20,1'),
        ],
    ],
];
