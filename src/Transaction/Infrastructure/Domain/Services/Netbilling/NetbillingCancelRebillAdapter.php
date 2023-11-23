<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTimeImmutable;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;

class NetbillingCancelRebillAdapter implements BaseNetbillingCancelRebillAdapter
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
     * NetbillingCancelRebillAdapter constructor.
     * @param NetbillingClient                     $netbillingClient
     * @param NetbillingCreditCardChargeTranslator $translator
     */
    public function __construct(NetbillingClient $netbillingClient, NetbillingCreditCardChargeTranslator $translator)
    {
        $this->client     = $netbillingClient;
        $this->translator = $translator;
    }

    /**
     * @param CancelRebillCommand $command
     * @param DateTimeImmutable  $requestDate
     *
     * @return NetbillingBillerResponse
     * @throws InvalidBillerResponseException
     * @throws LoggerException
     * @throws NetbillingServiceException
     * @throws Throwable
     */
    public function cancel(
        CancelRebillCommand $command,
        DateTimeImmutable $requestDate
    ): NetbillingBillerResponse {

        $response     = $this->client->cancelRebill($command);
        $responseDate = new DateTimeImmutable();

        return $this->translator->toCreditCardBillerResponse(
            $response,
            $requestDate,
            $responseDate
        );
    }
}
