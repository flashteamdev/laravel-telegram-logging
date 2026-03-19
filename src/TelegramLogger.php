<?php

namespace Logger;

use Monolog\Logger;

/**
 * Class TelegramLogger
 */
class TelegramLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array{driver: string, via: string, level: string}  $config
     * @return Logger
     */
    public function __invoke(array $config)
    {
        return new Logger(
            config('app.name'),
            [
                new TelegramHandler($config),
            ]
        );
    }
}
