<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Exception;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Code;

class AfterTaxDoesNotMatchWithAmountException extends InvalidPayloadException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::AFTER_TAX_DOES_NOT_MATCH_WITH_AMOUNT;

    /**
     * AfterTaxDoesNotMatchWithAmountException constructor.
     *
     * @param string          $taxAmountName Tax amount description
     * @param string          $amountName    Amount description
     * @param \Throwable|null $previous      Previews exception
     * @throws Exception
     */
    public function __construct(string $taxAmountName, string $amountName, \Throwable $previous = null)
    {
        parent::__construct($previous, $taxAmountName, $amountName);
    }
}
