<?php

namespace DriBots\Attachments;

use DriBots\Data\Attachments\PhotoAttachment;
use DriBots\Platforms\TelegramPlatform;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TelegramPhotoAttachment extends PhotoAttachment {
    public function __construct(private string $fileId, private TelegramPlatform $platform){
        parent::__construct("jpg");
    }

    public function getFileId(): string{
        return $this->fileId;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getPath(): string{
        return "https://api.telegram.org/file/bot{$this->platform->BOT_API_TOKEN}/".
            $this->platform->botApi->getFile($this->fileId)->getFilePath();
    }
}