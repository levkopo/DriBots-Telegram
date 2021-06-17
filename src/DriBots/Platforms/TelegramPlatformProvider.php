<?php


namespace DriBots\Platforms;


use CURLFile;
use DriBots\Data\Attachment;
use DriBots\Data\Attachments\PhotoAttachment;
use DriBots\Data\InlineQuery;
use DriBots\Data\InlineQueryResult;
use DriBots\Data\Message;
use DriBots\Data\User;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Inline\InputMessageContent\Text;
use TelegramBot\Api\Types\Inline\QueryResult\Article;

class TelegramPlatformProvider implements BasePlatformProvider {

    public function __construct(
        private BotApi $botApi
    ) {}

    public function sendMessage(int $toId, string $text, Attachment $attachment = null): Message|false {
        try {
            if($attachment instanceof PhotoAttachment){
                $message = $this->botApi->sendPhoto($toId, new CURLFile($attachment->path),
                    caption: $text);
            }else if($text!=='') {
                $message = $this->botApi->sendMessage($toId, $text);
            }else {
                return false;
            }

            return new Message(
                id: $message->getMessageId(),
                chatId: $message->getChat()->getId(),
                ownerId: $message->getFrom()!==null?
                    $message->getFrom()->getId()&$message->getChat()->getId():0,
                text: $message->getText()??$message->getCaption()
            );
        } catch (InvalidArgumentException|Exception) {}

        return false;
    }

    public function getUser(int $chatId, int $userId): User|false {
        $member = $this->botApi->getChatMember($chatId, $userId)
            ->getUser();
        return new User($member->getId(), $member->getUsername());
    }

    public function answerToQuery(InlineQuery $query, InlineQueryResult $inlineQueryResult): bool {
        try {
            $this->botApi->answerInlineQuery($query->id, [
                new Article("1", $inlineQueryResult->title,
                    description: $inlineQueryResult->description,
                    inputMessageContent: new Text($inlineQueryResult->messageText))]);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}