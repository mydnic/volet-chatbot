<?php

namespace Mydnic\VoletChatbot\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;

class ChatbotAgent implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function instructions(): string
    {
        return config('volet-chatbot.system_prompt');
    }

    public function provider(): string
    {
        return config('volet-chatbot.provider');
    }

    public function model(): string
    {
        return config('volet-chatbot.model');
    }
}
