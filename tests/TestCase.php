<?php

namespace Mydnic\VoletChatbot\Tests;

use Laravel\Ai\AiServiceProvider;
use Mydnic\VoletChatbot\VoletChatbotServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AiServiceProvider::class,
            VoletChatbotServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('volet-chatbot', require __DIR__.'/../config/volet-chatbot.php');
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            __DIR__.'/../vendor/laravel/ai/database/migrations'
        );
    }
}
