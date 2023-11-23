<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Validators;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;

class ExistingCreditCardInformation extends Information
{
    use Validators;

    /**
     * @var string
     */
    protected $cardHash;

    /**
     * Information constructor.
     * @param string|null $cardHash The credit card hash
     *
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(?string $cardHash)
    {
        $this->initCardHash($cardHash);
    }

    /**
     * @param string|null $cardHash Credit card hash
     *
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    private function initCardHash(?string $cardHash)
    {
        if (empty($cardHash)) {
            throw new MissingCreditCardInformationException('cardHash');
        }
        $this->cardHash = $cardHash;
    }

    /**
     * @return string
     */
    public function cardHash(): string
    {
        return $this->cardHash;
    }
}
