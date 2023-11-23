<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Pumapay\Domain\Model\PostbackResponse;
use ProBillerNG\Qysso\Application\NewSaleCommand;
use ProBillerNG\Qysso\Domain\Model\Exception\MalformedPayloadException;
use ProBillerNG\Qysso\Domain\Services\DigestValidationService;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Domain\Services\QyssoNewSaleAdapter as QyssoNewSaleAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\QyssoService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoReturnBillerResponse;

class QyssoTranslatingService implements QyssoService
{
    /** @var QyssoNewSaleAdapterInterface */
    protected $newSaleAdapter;

    /**
     * QyssoTranslatingService constructor.
     * @param QyssoNewSaleAdapterInterface $newSaleAdapter
     */
    public function __construct(
        QyssoNewSaleAdapterInterface $newSaleAdapter
    ) {
        $this->newSaleAdapter = $newSaleAdapter;
    }

    /**
     * @param array  $transactions
     * @param array  $taxArray
     * @param string $sessionId
     * @param string $clientIp
     * @param Member $member
     * @return QyssoNewSaleBillerResponse
     * @throws Exception
     */
    public function chargeNewSale(
        array $transactions,
        array $taxArray,
        string $sessionId,
        string $clientIp,
        Member $member
    ): QyssoNewSaleBillerResponse {
        /** @var ChargeTransaction $mainTransaction */
        $mainTransaction = $transactions[0];
        /** @var QyssoBillerSettings $billerSettings */
        $billerSettings = $mainTransaction->billerChargeSettings();
        /** @var Rebill $rebill */
        $rebill = $mainTransaction->chargeInformation()->rebill();

        $command = new NewSaleCommand(
            $mainTransaction->paymentType(),
            $mainTransaction->paymentMethod(),
            (string) $mainTransaction->chargeInformation()->amount(),
            (string) $mainTransaction->chargeInformation()->currency(),
            $member->fullName(),
            $member->email(),
            $member->address(),
            $member->city(),
            $member->zipCode(),
            $member->country(),
            $member->phone(),
            $clientIp,
            $billerSettings->notificationUrl(),
            (string) $mainTransaction->transactionId(),
            $member->memberId(),
            $billerSettings->redirectUrl(),
            $rebill ? (string) $mainTransaction->chargeInformation()->rebill()->amount() : null,
            $rebill ? (string) $mainTransaction->chargeInformation()->rebill()->frequency() : null,
            $rebill ? (string) $mainTransaction->chargeInformation()->rebill()->start() : null,
            null,
            env('BILLER_QYSSO_TEST_MODE', false)
        );

        return $this->newSaleAdapter->newSale($command, $billerSettings);
    }

    /**
     * @param string      $jsonPayload     The payload coming from qysso
     * @param string      $personalHashKey The merchant's hash key
     * @param string|null $transactionType The type of the transaction
     *
     * @return QyssoBillerResponse
     * @throws Exception
     * @throws MalformedPayloadException
     */
    public function translatePostback(
        string $jsonPayload,
        string $personalHashKey,
        ?string $transactionType = null
    ): QyssoBillerResponse {
        $decodedResponse = json_decode($jsonPayload, true);

        if (isset($decodedResponse['trans_id']) || $transactionType === PostbackResponse::CHARGE_TYPE_REBILL) {
            if (DigestValidationService::validatePostback($decodedResponse, $personalHashKey) == false) {
                Log::info('Could not validate Qysso signature');
                throw new MalformedPayloadException();
            }

            if (!isset($decodedResponse['reply_code'])) {
                Log::info('Qysso reply_code is missing from postback');
                throw new MalformedPayloadException();
            }

            return QyssoPostbackBillerResponse::create($jsonPayload);
        }

        if (isset($decodedResponse['TransID'])) {
            if (DigestValidationService::validateReturn($decodedResponse, $personalHashKey) == false) {
                Log::info('Could not validate Qysso signature');
                throw new MalformedPayloadException();
            }

            if (!isset($decodedResponse['Reply'])) {
                Log::info('Qysso Reply is missing from return');
                throw new MalformedPayloadException();
            }

            return QyssoReturnBillerResponse::create($jsonPayload);
        }

        throw new Exception('Invalid payload received from biller, it is not possible to translate.');
    }
}
