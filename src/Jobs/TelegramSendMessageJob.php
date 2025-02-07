<?php

namespace Logger\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramSendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $botToken,
        public string|int $chatId,
        public string $text,
        public ?string $host = null,
        public ?string $proxy = null,
        public ?string $threadId = null,
        /**
         * @see https://core.telegram.org/bots/api#sendmessage
         */
        public ?array $options = [],
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $text = Str::limit($this->text, 4000);

            $httpQuery = http_build_query(
                array_merge(
                    [
                        'text' => $text,
                        'chat_id' => $this->chatId,
                        // 'message_thread_id' => $this->threadId,
                        'parse_mode' => 'html',
                    ],
                    config('telegram-logger.options', []),
                    $this->options
                )
            );

            $url = $this->host.'/bot'.$this->botToken.'/sendMessage?'.$httpQuery;

            $res = Http::withOptions([
                ...($this->proxy ? ['proxy' => $this->proxy] : []),
            ])->get($url);

            if (! $res->json('ok') && $res->json('error_code') === 429) {
                $this->release($res->json('parameters.retry_after') + 1);
            }
        } catch (Exception $exception) {
            Log::channel('daily')->error($exception->getMessage());
        }
    }
}
