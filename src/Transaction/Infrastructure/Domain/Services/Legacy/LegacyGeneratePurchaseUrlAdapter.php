<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy;

use ProbillerNG\LegacyServiceClient\ApiException;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Services\LegacyNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\LegacyServiceResponseException;

class LegacyGeneratePurchaseUrlAdapter implements LegacyNewSaleAdapter
{
    /**
     * @var LegacyNewSaleClient
     */
    private $client;
    /**
     * @var LegacyNewSaleTranslator
     */
    private $translator;

    /**
     * LegacyNewSaleAdapter constructor.
     * @param LegacyNewSaleClient     $client     Client
     * @param LegacyNewSaleTranslator $translator Translator
     */
    public function __construct(LegacyNewSaleClient $client, LegacyNewSaleTranslator $translator)
    {
        $this->client     = $client;
        $this->translator = $translator;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param ChargesCollection $charges     Charges
     * @param Member            $member      Member
     * @return LegacyNewSaleBillerResponse
     * @throws ApiException
     * @throws Exception
     * @throws LegacyServiceResponseException
     * @throws InvalidChargeInformationException
     */
    public function newSale(
        ChargeTransaction $transaction,
        ChargesCollection $charges,
        ?Member $member
    ): LegacyNewSaleBillerResponse {
        $requestDateTime = new \DateTimeImmutable();

        $legacyServiceRequest = (new LegacyServiceNewSalePayloadBuilder())
            ->createPurchaseUrlPayload($transaction, $charges, $member);

        $response = $this->client->generatePurchaseUrl((string) $transaction->siteId(), $legacyServiceRequest);

        $responseDateTime = new \DateTimeImmutable();

        return $this->translator->translate(
            $response,
            $legacyServiceRequest,
            $requestDateTime,
            $responseDateTime
        );
    }
}
