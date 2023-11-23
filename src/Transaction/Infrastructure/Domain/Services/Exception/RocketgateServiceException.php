<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception;

use ProBillerNG\Transaction\Infrastructure\InfrastructureException as InfrastructureException;
use ProBillerNG\Transaction\Code;

class RocketgateServiceException extends InfrastructureException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::ROCKETGATE_SERVICE_EXCEPTION;

    /**
     * RocketgateServiceException constructor.
     *
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
