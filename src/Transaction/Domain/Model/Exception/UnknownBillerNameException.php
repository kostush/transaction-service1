<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use Throwable;
use ProBillerNG\Transaction\Code;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Domain\DomainException;

class UnknownBillerNameException extends DomainException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::UNKNOWN_BILLER_NAME_EXCEPTION;

    /**
     * UnknownBillerNameException constructor.
     *
     * @param string         $billerName Biller Name
     * @param Throwable|null $previous   Previous exception
     * @throws LoggerException
     */
    public function __construct(string $billerName, ?Throwable $previous = null)
    {
        parent::__construct($previous, $billerName);
    }
}
