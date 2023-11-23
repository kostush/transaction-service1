<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTimeImmutable;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;

class NetbillingUpdateRebillAdapter implements UpdateRebillNetbillingAdapter
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
     * NetbillingUpdateRebillAdapter constructor.
     * @param NetbillingClient                     $netbillingClient Client
     * @param NetbillingCreditCardChargeTranslator $translator       Translator
     */
    public function __construct(NetbillingClient $netbillingClient, NetbillingCreditCardChargeTranslator $translator)
    {
        $this->client     = $netbillingClient;
        $this->translator = $translator;
    }

    /**
     * @param UpdateRebillCommand $updateRebillCommand
     * @param DateTimeImmutable   $requestDate
     * @return NetbillingBillerResponse
     * @throws LoggerException
     * @throws InvalidBillerResponseException
     * @throws NetbillingServiceException
     */
    public function update(
        UpdateRebillCommand $updateRebillCommand,
        DateTimeImmutable $requestDate
    ): NetbillingBillerResponse {
        // Call netbilling handler
        $response = $this->client->updateRebill($updateRebillCommand);

        return $this->translator->toCreditCardBillerResponse(
            $response,
            $requestDate,
            new DateTimeImmutable()
        );
    }
}
