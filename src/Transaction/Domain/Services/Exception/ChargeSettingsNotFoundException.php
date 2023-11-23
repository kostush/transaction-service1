<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class ChargeSettingsNotFoundException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::CHARGE_SETTINGS_NOT_FOUND_EXCEPTION;

    /**
     * RetrieveQrCodeException constructor.
     *
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
