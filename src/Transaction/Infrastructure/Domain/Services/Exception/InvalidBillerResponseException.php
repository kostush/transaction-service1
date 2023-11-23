<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception;

use ProBillerNG\Transaction\Infrastructure\InfrastructureException as InfrastructureException;
use ProBillerNG\Transaction\Code;

class InvalidBillerResponseException extends InfrastructureException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_BILLER_RESPONSE_EXCEPTION;
}
