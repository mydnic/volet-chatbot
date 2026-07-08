<?php

namespace Mydnic\VoletChatbot;

use Mydnic\Volet\Features\BaseFeature;

class VoletChatbot extends BaseFeature
{
    public function getId(): string
    {
        return 'volet-chatbot';
    }

    public function getLabel(): string
    {
        return $this->label ?? 'Chat with us';
    }

    public function getIcon(): string
    {
        return 'https://api.iconify.design/lucide:message-circle.svg?color=%23888888';
    }

    public function getComponentName(): ?string
    {
        return 'volet-chatbot';
    }

    public function getScripts(): ?string
    {
        $scriptUrl = asset('vendor/volet/volet-chatbot.js');

        return "<script src=\"{$scriptUrl}\"></script>";
    }

    public function getConfig(): array
    {
        return [
            'routes' => [
                'send' => route('volet.chatbot.messages.store'),
            ],
            'labels' => trans('volet-chatbot::volet-chatbot'),
            'token' => csrf_token(),
        ];
    }
}
