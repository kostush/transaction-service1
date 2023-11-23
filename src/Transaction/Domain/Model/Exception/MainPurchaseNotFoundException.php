<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Code;

class MainPurchaseNotFoundException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::MAIN_PURCHASE_NOT_FOUND_EXCEPTION;

    /**
     * MissingChargeInformationException constructor.
     *
     * @param \Throwable|null $previous Previous exception
     * @throws Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
