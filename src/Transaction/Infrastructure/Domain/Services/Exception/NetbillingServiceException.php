<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Infrastructure\InfrastructureException as InfrastructureException;;
use Throwable;

class NetbillingServiceException extends InfrastructureException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::NETBILLING_SERVICE_EXCEPTION;

    /**
     * NetbillingServiceException constructor.
     * @param Throwable|null $previous previous exception
     * @throws Exception
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
