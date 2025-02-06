<?php

namespace Logger;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Logger\Jobs\TelegramSendMessageJob;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * Class TelegramHandler
 */
class TelegramHandler extends AbstractProcessingHandler
{
    /**
     * Logger config
     *
     * @var array{driver:string, via:string, level:string}
     */
    private array $config;

    /**
     * Bot API token
     */
    private string $botToken;

    /**
     * Chat id for bot
     */
    private string|int $chatId;

    /**
     * Message thread id for bot
     */
    private ?int $messageThreadId;

    /**
     * Application name
     */
    private string $appName;

    /**
     * Application environment
     */
    private string $appEnv;

    /**
     * TelegramHandler constructor.
     *
     * @param  array{driver:string, via:string, level:string}  $config
     */
    public function __construct(array $config)
    {
        // @phpstan-ignore-next-line
        parent::__construct($config['level'], true);

        // define variables for making Telegram request
        $this->config = $config;
        $this->botToken = $this->getConfigValue('token');
        $this->chatId = $this->getConfigValue('chat_id');
        $this->messageThreadId = $this->getConfigValue('message_thread_id');

        // define variables for text message
        $this->appName = config('app.name');
        $this->appEnv = config('app.env');
    }

    public function write(LogRecord $record): void
    {
        if (! $this->botToken || ! $this->chatId) {
            throw new InvalidArgumentException('Bot token or chat id is not defined for Telegram logger');
        }

        // trying to make request and send notification
        try {
            dispatch(new TelegramSendMessageJob(
                Str::of($this->botToken)->explode(',')->random(),
                $this->chatId,
                $this->formatText($record),
                $this->getConfigValue('api_host'),
                $this->getConfigValue('proxy'),
                $this->messageThreadId,
            ));
        } catch (Exception $exception) {
            Log::channel('daily')->error($exception->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("%message% %context% %extra%\n", null, false, true);
    }

    private function formatText(LogRecord $record): string
    {
        $recordInfo = $record->toArray();
        /** @var Exception|null $exception */
        $exception = $record->context['exception'] ?? null;
        $exceptionText = '';
        if ($exception) {
            $exceptionText = collect($exception->getTrace())
                ->take(5)
                ->map(function ($trace) {
                    $fileName = Str::of($trace['file'] ?? '')
                        ->explode('/')->take(-3)->join('/');
                    $line = $trace['line'] ?? '';

                    return $fileName.':'.$line;
                })
                ->join("\n");

            unset($recordInfo['context']['exception']);
        }

        return view(
            config('telegram-logger.template'),
            array_merge($recordInfo, [
                'exception' => $exceptionText,
                'appName' => $this->appName,
                'appEnv' => $this->appEnv,
                'formatted' => $record->formatted,
            ])
        )
            ->render();
    }

    private function getConfigValue(string $key, ?string $defaultConfigKey = null): string
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return config($defaultConfigKey ?: "telegram-logger.$key");
    }
}
