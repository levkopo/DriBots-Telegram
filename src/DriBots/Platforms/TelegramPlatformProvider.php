<?php


namespace DriBots\Platforms;


use DriBots\Data\Message;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TelegramPlatformProvider implements BasePlatformProvider {

    public function __construct(
        private BotApi $botApi
    ) {}

    public function sendMessage(int $toId, string $text): Message|false {
        try {
            $message = $this->botApi->sendMessage($toId, $text);
            return new Message(
                id: $message->getMessageId(),
                fromId: $message->getChat()->getId(),
                text: $message->getText()
            );
        } catch (InvalidArgumentException|Exception) {}

        return false;
    }
}