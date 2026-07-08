<?php

use Mydnic\VoletChatbot\Agents\ChatbotAgent;

it('streams a reply and a conversation id', function () {
    ChatbotAgent::fake(['Hello there!']);

    $response = $this->postJson('/volet/chatbot/messages', [
        'message' => 'Hi!',
    ]);

    $response->assertOk();

    $content = $response->streamedContent();

    expect($content)
        ->toContain('"type":"text_delta"')
        ->toContain('"type":"conversation"')
        ->toContain('[DONE]');
});

it('rejects an empty message', function () {
    $response = $this->postJson('/volet/chatbot/messages', [
        'message' => '',
    ]);

    $response->assertInvalid(['message']);
});

it('rejects a message over the configured length limit', function () {
    $response = $this->postJson('/volet/chatbot/messages', [
        'message' => str_repeat('a', config('volet-chatbot.max_message_length') + 1),
    ]);

    $response->assertInvalid(['message']);
});
