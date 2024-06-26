<?php

namespace Logger;

use Illuminate\Support\ServiceProvider;

/**
 * Class TelegramLoggerServiceProvider
 */
class TelegramLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/telegram-logger.php', 'telegram-logger');
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../views', 'laravel-telegram-logging');
        $this->publishes([__DIR__.'/../views' => base_path('resources/views/vendor/laravel-telegram-logging')], 'views');
        $this->publishes([__DIR__.'/../config/telegram-logger.php' => config_path('telegram-logger.php')], 'config');
    }
}
