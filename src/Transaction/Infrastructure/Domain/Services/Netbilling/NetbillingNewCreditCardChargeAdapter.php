<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\NetbillingChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;

class NetbillingNewCreditCardChargeAdapter implements NetbillingChargeAdapter
{
    /**
     * @var NetbillingClient
     */
    protected $client;

    /**
     * @var NetbillingCreditCardChargeTranslator
     */
    protected $translator;

    /**
     * NetbillingNewCreditCardChargeAdapter constructor.
     * @param NetbillingClient                     $netbilling Client
     * @param NetbillingCreditCardChargeTranslator $translator Translator
     */
    public function __construct(
        NetbillingClient $netbilling,
        NetbillingCreditCardChargeTranslator $translator
    ) {
        $this->client     = $netbilling;
        $this->translator = $translator;
    }

    /**
     * @param CreditCardChargeCommand $command     Command
     * @param DateTimeImmutable       $requestDate Request date
     *
     * @return NetbillingBillerResponse
     * @throws NetbillingServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidBillerResponseException
     * @throws \Throwable
     */
    public function charge(
        CreditCardChargeCommand $command,
        DateTimeImmutable $requestDate
    ): NetbillingBillerResponse {
        $response = $this->client->chargeNewCreditCard($command);

        // Call the translator
        return $this->translator->toCreditCardBillerResponse(
            $response,
            $requestDate,
            new DateTimeImmutable()
        );
    }
}
