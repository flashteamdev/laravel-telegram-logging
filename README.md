# Laravel Telegram logger

Send logs to Telegram chat via Telegram bot

## Install

```

composer require flashteamdev/laravel-telegram-logging

```

Define Telegram Bot Token and chat id (users telegram id) and set as environment parameters.
Add to <b>.env</b>

```
TELEGRAM_LOGGER_BOT_TOKEN="id1:token1,id2:token2"
TELEGRAM_LOGGER_CHAT_ID=chat_id
```

Add to <b>config/logging.php</b> file new channel:

```php
'telegram' => [
    'driver' => 'custom',
    'via'    => Logger\TelegramLogger::class,
    'level'  => 'debug',
]
```

If your default log channel is a stack, you can add it to the <b>stack</b> channel like this

```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single', 'telegram'],
]
```

Or you can simply change the default log channel in the .env

```
LOG_CHANNEL=telegram
```

Publish config file and views

```
php artisan vendor:publish --provider "Logger\TelegramLoggerServiceProvider"
```

## Telegram Logging Formats

You can choose among two different formats that you can specify in the `.env` file like this :

```
# Use a minimal log template
TELEGRAM_LOGGER_TEMPLATE = laravel-telegram-logging::minimal

# Or use the backward compatible one (default setting used even without inserting this row)
TELEGRAM_LOGGER_TEMPLATE = laravel-telegram-logging::standard
```

It is possible to create other blade templates and reference them in the `TELEGRAM_LOGGER_TEMPLATE` entry

## Create bot

For using this package you need to create Telegram bot

1. Go to @BotFather in the Telegram
2. Send `/newbot`
3. Set up name and bot-name for your bot.
4. Get token and add it to your .env file (it is written above)
5. Go to your bot and send `/start` message

## Change log template at runtime

1. Change config for template.

```php
config(['telegram-logger.template'=>'laravel-telegram-logging::custom'])
```

2. Use `Log` as usual.

## Configuring a different chat id or token per channel

1. Add `chat_id` or `token` to channels in `config/logging.php`. Overrides `config('telegram.chat_id')`.

```php
[
    'channels' => [
        [
            'company' => [
                'driver' => 'custom',
                'via' => TelegramLogger::class,
                'chat_id' => env('TELEGRAM_COMPANY_CHAT_ID'),
                'token' => env('TELEGRAM_COMPANY_BOT_TOKEN'),
                'level' => 'debug'
            ],

            'operations' => [
                'driver' => 'custom',
                'via' => TelegramLogger::class,
                'chat_id' => env('TELEGRAM_OPERATIONS_CHAT_ID'),
                'token' => env('TELEGRAM_OPERATIONS_BOT_TOKEN'),
                'level' => 'debug'
            ]
        ]
    ]
]
```

2. Use `Log` as usual.

## Proxy support

To use a proxy server, set the variable in the .env

```
TELEGRAM_LOGGER_PROXY=proxy_server.com:port
```
