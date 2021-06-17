<?php


namespace DriBots\Platforms;


use DriBots\Data\Attachment;
use DriBots\Data\Attachments\PhotoAttachment;
use DriBots\Data\Event;
use DriBots\Data\Message;
use DriBots\Data\User;
use JetBrains\PhpStorm\Pure;
use JsonException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TelegramPlatform extends BasePlatform {
    private array $data;
    private TelegramPlatformProvider $telegramPlatformProvider;
    private BotApi $botApi;

    public function __construct(
        private string $BOT_API_TOKEN
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

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getAttachment(array $message): ?Attachment {
        if($message===null) {
            return null;
        }

        if(isset($message['photo'])){
            $photo = $message['photo'][(int) (count($message['photo'])/2)];

            return new PhotoAttachment(
                "https://api.telegram.org/file/bot$this->BOT_API_TOKEN/".
                    $this->botApi->getFile($photo['file_id'])->getFilePath(),
                "jpg"
            );
        }

        return null;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function parseMessage(array $message): Message {
        return new Message(
            id: $message['message_id'],
            fromId: $message['chat']['id'],
            ownerId: $message['from']['id']??0,
            text: $message['text']??$message['caption']??"",
            attachment: $this->getAttachment($message),
            user: isset($message['from'])?$this->parseUser($message['from']):null
        );
    }

    public function parseUser(array $user): User|null{
        return new User(
            id: $user['id'],
            username: $user['username']
        );
    }
}