<?php
namespace Otus\Diag;

use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\FileExceptionHandlerLog;

class FileExceptionHanlderLogCustom extends FileExceptionHandlerLog
{
    /**
     * @param $exception
     * @param $logType
     * @return void
     * Метод производит запись лога ошибки в файл
     */
    public function write($exception, $logType)
    {
        $text = ExceptionHandlerFormatter::format($exception, false, $this->level);

        $context = [
            'type' => static::logTypeToString($logType),
        ];

        $logLevel = static::logTypeToLevel($logType);

        $message = "OTUS - {date} - Host: {host} - {type} - {$text}\n";

        $this->logger->log($logLevel, $message, $context);
    }
}
