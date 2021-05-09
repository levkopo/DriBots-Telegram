<?php


namespace DriBots\Platforms;


use DriBots\Data\Attachment;
use DriBots\Data\Attachments\PhotoAttachment;
use DriBots\Data\Event;
use DriBots\Data\Message;
use JetBrains\PhpStorm\Pure;
use JsonException;
use TelegramBot\Api\BotApi;

class TelegramPlatform extends BasePlatform {
    private array $data;
    private TelegramPlatformProvider $telegramPlatformProvider;
    private BotApi $botApi;

    public function __construct(
        string $BOT_API_TOKEN
    ) {
        $this->botApi = new BotApi($BOT_API_TOKEN);
        $this->telegramPlatformProvider = new TelegramPlatformProvider(
            $this->botApi
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

    public function getAttachment(array $message): ?Attachment {
        if($message===null) {
            return null;
        }else if(isset($message['photo'])){
            $photo = $message['photo'][(int) (sizeof($message['photo'])/2)];

            return new PhotoAttachment(
                $this->botApi->getFile($photo['file_id'])->getFilePath(),
                "jpg"
            );
        }

        return null;
    }

    #[Pure] public function parseMessage(array $message): Message {
        return new Message(
            id: $message['message_id'],
            fromId: $message['chat']['id'],
            ownerId: isset($message['from'])?
                $message['from']['id']&$message['chat']['id']:0,
            text: $message['text'],
            attachment: $this->getAttachment($message)
        );
    }
}