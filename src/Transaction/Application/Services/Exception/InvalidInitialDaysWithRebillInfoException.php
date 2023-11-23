<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Exception;

use ProBillerNG\Transaction\Application\Services\ApplicationException;
use ProBillerNG\Transaction\Code;

/**
 * Class InvalidPayloadException
 * @package ProBillerNG\Transaction\Application\Services\Exception
 */
class InvalidInitialDaysWithRebillInfoException extends ApplicationException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_INITIAL_DAYS_EXCEPTION;
}