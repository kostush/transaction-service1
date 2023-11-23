<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class InvalidStatusNameException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_STATUS_NAME;

    /**
     * InvalidBillerInteractionTypeException constructor.
     * @param string     $received Biller Id Received
     * @param \Throwable $previous Previous error
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $received, \Throwable $previous = null)
    {
        parent::__construct($previous, 'Received status' . $received);
    }
}
