<?php

namespace Mydnic\VoletChatbot;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class VoletChatbotServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('volet-chatbot')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasRoute('api')
            ->hasAssets();
    }

    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/dist' => public_path('vendor/volet'),
            ], 'volet-assets');
        }
    }
}
