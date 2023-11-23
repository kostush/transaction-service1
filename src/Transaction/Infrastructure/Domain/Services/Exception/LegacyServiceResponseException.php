<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Infrastructure\InfrastructureException;

class LegacyServiceResponseException extends InfrastructureException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::LEGACY_SERVICE_RESPONSE_EXCEPTION;

    /**
     * LegacyServiceResponseException constructor.
     * @param \Throwable|null $previous previous exception
     * @throws Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
