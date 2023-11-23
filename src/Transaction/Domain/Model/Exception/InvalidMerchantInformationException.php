<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;

class InvalidMerchantInformationException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_MERCHANT_INFORMATION_EXCEPTION;

    /**
     * InvalidMerchantInformationException constructor.
     *
     * @param string          $parameter Missing parameter name
     * @param \Throwable|null $previous  Previews exception
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $parameter, \Throwable $previous = null)
    {
        parent::__construct($previous, $parameter);
    }
}
