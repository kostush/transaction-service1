<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class InvalidThreedsVersionException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_THREEDS_VERSION;

    /**
     * InvalidBillerInteractionTypeException constructor.
     * @param int|null   $received Version received
     * @param \Throwable $previous Previous error
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(?int $received, \Throwable $previous = null)
    {
        parent::__construct($previous, 'Received threeds version ' . $received);
    }
}
