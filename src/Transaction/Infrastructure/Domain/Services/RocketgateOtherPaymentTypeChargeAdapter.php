<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Exception;
use DateTimeImmutable;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCheckCommand;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateOtherPaymentTypeResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\OtherPaymentTypeChargeClient;

/**
 * Class RocketgateOtherPaymentTypeChargeAdapter
 * @package ProBillerNG\Transaction\Infrastructure\Domain\Services
 */
class RocketgateOtherPaymentTypeChargeAdapter extends ChargeAdapter
{
    /**
     * @var OtherPaymentTypeChargeClient
     */
    protected $client;

    /**
     * RocketgateCreditCardChargeAdapter constructor.
     * @param OtherPaymentTypeChargeClient $rocketgate Client
     */
    public function __construct(OtherPaymentTypeChargeClient $rocketgate)
    {
        $this->client = $rocketgate;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     *
     * @return RocketgateBillerResponse
     * @throws Exception
     */
    public function charge(ChargeTransaction $transaction): RocketgateBillerResponse
    {
        try {
            $command = (new RocketgateOtherPaymentPayloadBuilder())
                ->createRocketgateNewCheckChargeCommandFromChargeTransaction($transaction);

            $requestDate = new DateTimeImmutable();
            $response    = $this->client->chargeOtherPaymentType($command);

            return RocketgateOtherPaymentTypeResponse::create($requestDate, $response, new DateTimeImmutable());
        } catch (Exception $exception) {
            // We have not implemented circuit breaker in order to buy time and
            // we are moving soon to transaction service v2.
            Log::error('Exception from biller', ['response' => $response ?? null, 'exception' => $exception]);
            return RocketgateOtherPaymentTypeResponse::createAbortedResponse(new InvalidBillerResponseException());
        }
    }
}
