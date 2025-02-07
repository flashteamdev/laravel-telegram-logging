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
        // Unique identifier for the target chat or username of the target channel (in the format @channelusername)
        public string|int $chatId,
        // Text of the message to be sent, 1-4096 characters after entities parsing
        public string $text,
        // Unique identifier of the business connection on behalf of which the message will be sent
        public ?string $businessConnectionId = null,
        // Unique identifier for the target message thread (topic) of the forum; for forum supergroups only
        public ?int $messageThreadId = null,
        // Mode for parsing entities in the message text. See formatting options for more details.
        public ?string $parseMode = 'html',
        // A JSON-serialized list of special entities that appear in message text, which can be specified instead of parse_mode
        public ?array $entities = null,
        // Link preview generation options for the message
        public ?array $linkPreviewOptions = null,
        // Sends the message silently. Users will receive a notification with no sound.
        public ?bool $disableNotification = null,
        // Protects the contents of the sent message from forwarding and saving
        public ?bool $protectContent = null,
        // Pass True to allow up to 1000 messages per second, ignoring broadcasting limits for a fee of 0.1 Telegram Stars per message. The relevant Stars will be withdrawn from the bot's balance
        public ?bool $allowPaidBroadcast = null,
        // Unique identifier of the message effect to be added to the message; for private chats only
        public ?string $messageEffectId = null,
        // Description of the message to reply to
        public ?array $replyParameters = null,
        // Additional interface options. A JSON-serialized object for an inline keyboard, custom reply keyboard, instructions to remove a reply keyboard or to force a reply from the user
        public ?array $replyMarkup = null,
        // Selfhosted Telegram API host
        public ?string $host = 'https://api.telegram.org',
        // Proxy server
        public ?string $proxy = null,
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
            $httpQuery = http_build_query(
                array_merge(
                    [
                        'chat_id' => $this->chatId,
                        'text' => Str::limit($this->text, 4090),
                        'business_connection_id' => $this->businessConnectionId,
                        'message_thread_id' => $this->messageThreadId,
                        'parse_mode' => $this->parseMode,
                        'entities' => $this->entities,
                        'link_preview_options' => $this->linkPreviewOptions,
                        'disable_notification' => $this->disableNotification,
                        'protect_content' => $this->protectContent,
                        'allow_paid_broadcast' => $this->allowPaidBroadcast,
                        'message_effect_id' => $this->messageEffectId,
                        'reply_parameters' => $this->replyParameters,
                        'reply_markup' => $this->replyMarkup,
                    ],
                    $this->options,
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
