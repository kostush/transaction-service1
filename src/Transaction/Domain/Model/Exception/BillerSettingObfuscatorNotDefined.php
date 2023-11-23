<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\DomainException;

class BillerSettingObfuscatorNotDefined extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::BILLER_OBFUSCATOR_NOT_DEFINED;

    /**
     * BillerSettingObfuscatorNotDefined constructor.
     * @param string          $billerName Biller name
     * @param \Throwable|null $previous   Previous Error
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $billerName, \Throwable $previous = null)
    {
        parent::__construct($previous, $billerName);
    }
}
