<?php

use Mydnic\VoletChatbot\Agents\ChatbotAgent;

return [
    // The agent class that answers the widget. Point this at your own class
    // (extending the default one, or implementing Laravel\Ai\Contracts\Agent
    // yourself) to add tools, MCP, structured output, etc. See laravel/ai
    // docs, nothing chatbot-specific is needed for that.
    'agent' => ChatbotAgent::class,

    // Which entry in config('ai.php')'s "providers" array to use. Defaults
    // to "openai", which talks to OpenAI's own API — override OPENAI_URL in
    // your .env to point it at any other OpenAI-compatible endpoint instead
    // (your own Laravel app's backend, a self-hosted model, etc).
    'provider' => env('VOLET_CHATBOT_PROVIDER', 'openai'),

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
