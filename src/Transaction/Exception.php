<?php

namespace ProBillerNG\Transaction;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\Exception\SensitiveInformationException;

abstract class Exception extends \Exception
{
    /**
     * @var int $code
     */
    protected $code = Code::TRANSACTION_EXCEPTION;

    /**
     * Exception constructor.
     *
     * @param \Throwable|null $previous Previews Error
     * @param array           ...$args  Other parameters
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(?\Throwable $previous = null, ...$args)
    {
        $message = $this->buildMessage($args);

        parent::__construct($message, $this->code, $previous);

        // Log previous exception
        if (null !== $previous) {
            Log::error('Previous exception', ['message' => $previous->getMessage(), 'code' => $previous->getCode()]);
        }

        // Log current exception
        if (!$this instanceof SensitiveInformationException) {
            Log::logException($this);
        } else {
            Log::error(
                'EXCEPTION',
                [
                    'MESSAGE' => $this->getMessage(),
                    'TRACE' => get_class($this) . ' occurred on file: '
                               . $this->getFile() . ' on line: ' . $this->getLine()
                ]
            );
        }
    }

    /**
     * @param array $args Argument Array
     * @return string
     */
    private function buildMessage($args): string
    {
        $message = Code::getMessage($this->code);

        if (!empty($args)) {
            $difference = count($args) - substr_count($message, '%');

            if ($difference > 0) {
                $message .= ' - Info:' . str_repeat(' [%s],', $difference);
                $message  = rtrim($message, ',');
            }

            $message = \sprintf($message, ...$args);
        }

        return $message;
    }
}
