<?php

use Mydnic\VoletChatbot\VoletChatbot;

// Iconify only serves "<set>/<icon>.svg"; the "<set>:<icon>.svg" form returns 404.
it('uses an iconify icon url in the path form', function () {
    expect((new VoletChatbot)->getIcon())
        ->toBe('https://api.iconify.design/lucide/message-circle.svg?color=%23888888');
});
