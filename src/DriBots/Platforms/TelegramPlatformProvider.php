<?php


namespace DriBots\Platforms;


use CURLFile;
use DriBots\Data\Attachment;
use DriBots\Data\Attachments\PhotoAttachment;
use DriBots\Data\Message;
use DriBots\Data\User;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TelegramPlatformProvider implements BasePlatformProvider {

    public function __construct(
        private BotApi $botApi
    ) {}

    public function sendMessage(int $toId, string $text, Attachment $attachment = null): Message|false {
        try {
            if($text!=='') {
                $message = $this->botApi->sendMessage($toId, $text);
            }


            if($attachment instanceof PhotoAttachment){
                $message = $this->botApi->sendPhoto($toId, new CURLFile($attachment->path));
            }

            return new Message(
                id: $message->getMessageId(),
                fromId: $message->getChat()->getId(),
                ownerId: $message->getFrom()!==null?
                    $message->getFrom()->getId()&$message->getChat()->getId():0,
                text: $message->getText()
            );
        } catch (InvalidArgumentException|Exception) {}

        return false;
    }

    public function getUser(int $userId): User|false {
        return false;
    }
}