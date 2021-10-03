<?php


namespace DriBots\Platforms;


use CURLFile;
use DriBots\Attachments\TelegramPhotoAttachment;
use DriBots\Data\Attachment;
use DriBots\Data\Attachments\PhotoAttachment;
use DriBots\Data\InlineQuery;
use DriBots\Data\InlineQueryResult;
use DriBots\Data\Message;
use DriBots\Data\User;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Inline\InputMessageContent\Text;
use TelegramBot\Api\Types\Inline\QueryResult\Article;

class TelegramPlatformProvider implements BasePlatformProvider {

    public function __construct(
        private TelegramPlatform $platform
    ) {}

    public function sendMessage(int $chatId, string $text, Attachment $attachment = null): Message|false {
        try {
            if($attachment instanceof PhotoAttachment){
                $message = $this->platform->botApi->sendPhoto($chatId, new CURLFile($attachment->getPath()),
                    caption: $text);
            }else if($text!=='') {
                $message = $this->platform->botApi->sendMessage($chatId, $text);
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
        $member = $this->platform->botApi->getChatMember($chatId, $userId)
            ->getUser();
        return new User($member->getId(), $member->getUsername());
    }

    public function answerToQuery(InlineQuery $query, InlineQueryResult $inlineQueryResult): bool {
        try {
            $this->platform->botApi->answerInlineQuery($query->id, [
                new Article("1", $inlineQueryResult->title,
                    description: $inlineQueryResult->description,
                    inputMessageContent: new Text($inlineQueryResult->messageText))]);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function getAttachmentFromFileId(string $fileId): Attachment|false {
        return new TelegramPhotoAttachment($fileId, $this->platform);
    }
}