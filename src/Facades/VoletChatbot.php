<?php

namespace Mydnic\VoletChatbot\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mydnic\VoletChatbot\VoletChatbot
 */
class VoletChatbot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Mydnic\VoletChatbot\VoletChatbot::class;
    }
}
