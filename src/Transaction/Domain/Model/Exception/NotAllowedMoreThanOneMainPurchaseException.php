<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Code;

class NotAllowedMoreThanOneMainPurchaseException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::NOT_ALLOWED_MORE_THAN_ONE_MAIN_PURCHASE_EXCEPTION;

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
