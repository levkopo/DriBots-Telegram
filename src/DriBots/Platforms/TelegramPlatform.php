<?php


namespace DriBots\Platforms;


use DriBots\Data\Event;
use DriBots\Data\Message;
use JetBrains\PhpStorm\Pure;
use JsonException;
use TelegramBot\Api\BotApi;

class TelegramPlatform extends BasePlatform {
    private array $data;
    private TelegramPlatformProvider $telegramPlatformProvider;

    public function __construct(
        string $BOT_API_TOKEN
    ) {
        $this->telegramPlatformProvider = new TelegramPlatformProvider(
            new BotApi($BOT_API_TOKEN)
        );
    }

    public function requestIsAccept(): bool {
        try {
            $this->data = json_decode(file_get_contents("php://input"),
                true, 512, JSON_THROW_ON_ERROR);
            return isset($this->data['update_id']);
        }catch (JsonException){}

        return false;
    }

    public function getName(): string {
        return "telegram";
    }

    #[Pure] public function getEvent(): Event|false {
        if(isset($this->data['message'])) {
            return Event::NEW_MESSAGE($this->parseMessage($this->data['message']));
        }

        return false;
    }


    public function getPlatformProvider(): ?BasePlatformProvider {
        return $this->telegramPlatformProvider;
    }

    #[Pure] public function parseMessage(array $message): Message {
        return new Message(
            id: $message['message_id'],
            fromId: $message['chat']['id'],
            text: $message['text']
        );
    }
}