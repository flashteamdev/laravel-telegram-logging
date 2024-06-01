<?php

namespace Logger\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramSendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $botToken,
        public string|int $chatId,
        public string $text,
        public ?string $host = null,
        public ?string $proxy = null,
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

            $httpQuery = http_build_query(array_merge(
                [
                    'text' => $text,
                    'chat_id' => $this->chatId,
                    'parse_mode' => 'html',
                ],
                config('telegram-logger.options', [])
            ));

            $url = $this->host.'/bot'.$this->botToken.'/sendMessage?'.$httpQuery;

            if (! empty($this->proxy)) {
                $context = stream_context_create([
                    'http' => [
                        'proxy' => $this->proxy,
                    ],
                ]);
                file_get_contents($url, false, $context);
            } else {
                file_get_contents($url);
            }
        } catch (Exception $exception) {
            Log::channel('single')->error($exception->getMessage());
        }
    }
}
