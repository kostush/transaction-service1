<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class MissingSiteIdForCrossSaleException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::MISSING_SITE_ID_FOR_CROSS_SALE_EXCEPTION;

    /**
     * MissingSiteIdForCrossSaleException constructor.
     *
     * @param \Throwable|null $previous Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct($previous);
    }
}
