<?php

use Illuminate\Support\Facades\Route;
use Mydnic\VoletChatbot\Http\Controllers\ChatController;

Route::prefix('volet')->group(function () {

    Route::prefix(config('volet-chatbot.routes.prefix'))
        ->middleware(config('volet-chatbot.routes.middleware'))
        ->group(function () {
            Route::post('/messages', [ChatController::class, 'store'])->name('volet.chatbot.messages.store');
        });

});
