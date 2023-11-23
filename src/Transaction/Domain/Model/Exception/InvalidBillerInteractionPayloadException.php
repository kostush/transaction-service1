<?php

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class InvalidBillerInteractionPayloadException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::BILLER_INTERACTION_PAYLOAD_INVALID;
}
