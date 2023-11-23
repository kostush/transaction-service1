<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class RetrieveQrCodeException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::RETRIEVE_QR_CODE_EXCEPTION;

    /**
     * RetrieveQrCodeException constructor.
     *
     * @param string          $reason   Reason
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $reason, \Throwable $previous = null)
    {
        parent::__construct($previous, $reason);
    }
}
