# A chatbot feature for Volet

Adds a chat UI to your [Volet](https://github.com/mydnic/volet) widget, backed by any OpenAI-compatible endpoint: OpenAI itself, or your own Laravel app's backend.

Built on Laravel's official [`laravel/ai`](https://github.com/laravel/ai) package. This package only wires that up to Volet's widget host; everything else (providers, streaming, conversation storage, tools, testing fakes) is `laravel/ai`'s own surface.

## Installation

```bash
composer require mydnic/volet-chatbot
```

Publish `laravel/ai`'s config and migrations (conversation history lives in its `agent_conversations` / `agent_conversation_messages` tables — this package does not add its own):

```bash
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
php artisan migrate
```

Publish this package's assets and config:

```bash
php artisan vendor:publish --tag="volet-assets" --force
php artisan vendor:publish --tag="volet-chatbot-config"
```

## Configuration

In `config/ai.php`, point the `openai-compatible` provider at your endpoint (or use OpenAI directly via the `openai` provider):

```dotenv
OPENAI_COMPATIBLE_URL=https://your-backend.test/v1
OPENAI_COMPATIBLE_API_KEY=sk-...
```

The API key only ever lives server-side in this config. It is never sent to the widget's JavaScript.

In `config/volet-chatbot.php`, set the provider/model/system prompt and the request rate limit:

```php
'provider' => env('VOLET_CHATBOT_PROVIDER', 'openai-compatible'),
'model' => env('VOLET_CHATBOT_MODEL', 'gpt-4o-mini'),
'system_prompt' => env('VOLET_CHATBOT_SYSTEM_PROMPT', 'You are a helpful assistant.'),
```

This widget is reachable by anyone who loads the page where you are loading Volet. Every message is a paid API call. The `throttle` middleware in `config('volet-chatbot.routes.middleware')` is keyed by user id (falling back to IP), tune `VOLET_CHATBOT_RATE_LIMIT` (default `20,1`) to your budget.

## Registering the feature

In your `VoletServiceProvider` (see the main Volet package's README for the quickstart):

```php
use Mydnic\VoletChatbot\VoletChatbot;

$volet->register(
    (new VoletChatbot)
        ->setLabel('Chat with us')
);
```

## Extending the agent — tools, MCP, structured output

The widget's replies are produced by `config('volet-chatbot.agent')`, which defaults to `Mydnic\VoletChatbot\Agents\ChatbotAgent`. To add tools, MCP servers, provider failover, or anything else `laravel/ai` supports, don't modify this package. Extend the agent in your own app and point the config at it:

```php
namespace App\Ai\Agents;

use Laravel\Ai\Contracts\HasTools;
use Mydnic\VoletChatbot\Agents\ChatbotAgent;

class SupportAgent extends ChatbotAgent implements HasTools
{
    public function tools(): array
    {
        return [
            new \App\Ai\Tools\LookupOrderStatus,
        ];
    }
}
```

```php
// config/volet-chatbot.php
'agent' => \App\Ai\Agents\SupportAgent::class,
```

See `laravel/ai`'s own docs for `php artisan make:tool`, MCP servers, structured output, and provider failover. None of it needs a Volet-specific bridge.

## Conversation history

Guests are tracked by a `conversation_id` minted on the first message and returned to the client, which stores it (e.g. `localStorage`) and sends it back on `continue`. No account or cookie is required. If a visitor is authenticated, their `user_id` is recorded alongside the conversation automatically.

## Scope

Not included in v1, by design: function/tool calling out of the box (bring your own via agent extension above), image/vision input, moderation of outbound messages, human handoff. Open an issue if you need one of these before building it yourself.
