<?php


namespace DriBots\Platforms;

use DriBots\Attachments\TelegramPhotoAttachment;
use DriBots\Data\Attachment;
use DriBots\Data\Event;
use DriBots\Data\InlineQuery;
use DriBots\Data\Message;
use DriBots\Data\User;
use JetBrains\PhpStorm\Pure;
use JsonException;
use TelegramBot\Api\BotApi;

class TelegramPlatform extends BasePlatform {
    private array $data;
    private TelegramPlatformProvider $telegramPlatformProvider;
    public BotApi $botApi;

    public function __construct(
        public string $BOT_API_TOKEN
    ) {
        $this->botApi = new BotApi($BOT_API_TOKEN);
        $this->telegramPlatformProvider = new TelegramPlatformProvider($this);
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

    public function getEvent(): Event|false {
        if(isset($this->data['message'])) {
            return Event::NEW_MESSAGE($this->parseMessage($this->data['message']));
        }else if(isset($this->data['inline_query'])){
            return Event::INLINE_QUERY(new InlineQuery(
                id: $this->data['inline_query']['id'],
                user: $this->parseUser($this->data['inline_query']['from']),
                query: $this->data['inline_query']['query'],
            ));
        }

        return false;
    }


    public function getPlatformProvider(): BasePlatformProvider {
        return $this->telegramPlatformProvider;
    }

    public function getAttachment(array $message): ?Attachment {
        if(isset($message['photo'])){
            $photo = $message['photo'][(int) (count($message['photo'])/2)];

            return new TelegramPhotoAttachment($photo['file_id'], $this);
        }

        return null;
    }

    public function parseMessage(array $message): Message {
        return new Message(
            id: $message['message_id'],
            chatId: $message['chat']['id'],
            ownerId: $message['from']['id']??0,
            text: $message['text']??$message['caption']??"",
            attachment: $this->getAttachment($message),
            user: isset($message['from'])?$this->parseUser($message['from']):null
        );
    }

    #[Pure] public function parseUser(array $user): User|null{
        return new User(
            id: $user['id'],
            username: $user['username']
        );
    }
}