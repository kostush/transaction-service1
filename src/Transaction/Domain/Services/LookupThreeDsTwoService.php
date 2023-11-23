<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\BI\LookupRequest;
use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaRocketgate;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateLookupThreeDsTwoBillerResponse;

class LookupThreeDsTwoService
{
    /**
     * @var LookupThreeDsTwoTranslatingService
     */
    public $lookupTranslatingService;

    /**
     * @var ChargeService
     */
    public $chargeService;

    /**
     * @var BILoggerService
     */
    protected $biLoggerService;

    /**
     * LookupThreeDsTwoService constructor.
     * @param LookupThreeDsTwoTranslatingService $lookupTranslatingService LookupTranslatingService
     * @param ChargeService                      $chargeService            Charge service
     * @param BILoggerService                    $biLoggerService          BI logger service
     */
    public function __construct(
        LookupThreeDsTwoTranslatingService $lookupTranslatingService,
        ChargeService $chargeService,
        BILoggerService $biLoggerService
    ) {
        $this->lookupTranslatingService = $lookupTranslatingService;
        $this->chargeService            = $chargeService;
        $this->biLoggerService          = $biLoggerService;
    }

    /**
     * @param ChargeTransaction $transaction            Previous transaction
     * @param string            $cardNumber             Card number
     * @param string            $expirationMonth        Expiration month
     * @param string            $expirationYear         Expiration year
     * @param string            $cvv                    Cvv
     * @param string            $deviceFingerprintingId Device fingerprinting id
     * @param string            $returnUrl              Return url
     * @param string            $merchantAccount        Merchant account
     * @param bool              $isNSFSupported
     *
     * @return ChargeTransaction
     * @throws LoggerException
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidThreedsVersionException
     */
    public function performTransaction(
        ChargeTransaction $transaction,
        string $cardNumber,
        string $expirationMonth,
        string $expirationYear,
        string $cvv,
        string $deviceFingerprintingId,
        string $returnUrl,
        string $merchantAccount,
        bool   $isNSFSupported
    ): ChargeTransaction {
        /** @var RocketgateLookupThreeDsTwoBillerResponse $lookupBillerResponse */
        $lookupBillerResponse = $this->lookupTranslatingService->performLookup(
            $transaction,
            $cardNumber,
            $expirationMonth,
            $expirationYear,
            $cvv,
            $deviceFingerprintingId,
            $returnUrl,
            $merchantAccount
        );

        return $this->manageTransactionByLookupResponse($lookupBillerResponse, $transaction, $isNSFSupported);
    }

    /**
     * @param RocketgateLookupThreeDsTwoBillerResponse $billerResponse RocketgateLookupThreeDsTwoBillerResponse
     * @param ChargeTransaction                        $transaction    Previous transaction
     * @param bool                                     $isNSFSupported Is nsf supported.
     * @return ChargeTransaction
     * @throws IllegalStateTransitionException
     * @throws InvalidChargeInformationException
     * @throws LoggerException
     * @throws InvalidThreedsVersionException
     * @throws \JsonException
     */
    public function manageTransactionByLookupResponse(
        RocketgateLookupThreeDsTwoBillerResponse $billerResponse,
        ChargeTransaction $transaction,
        bool $isNSFSupported
    ): ChargeTransaction {
        Log::info(
            'Managing transaction by Rocketgate 3ds2 lookup biller response ',
            [
                'transactionId'      => (string) $transaction->transactionId(),
                'billerResponseCode' => $billerResponse->code()
            ]
        );

        // This variable will store the initial biller response so that we can check before writing DWS event
        $shouldRetryWithoutThreeD = $billerResponse->shouldRetryWithoutThreeD();

        if ($shouldRetryWithoutThreeD) {
            Log::info('Retrying transaction without 3DS');

            $transaction->updateTransactionBillerInteraction($billerResponse);

            // mark transaction not to be using 3D
            $transaction->updateTransactionWith3D(false);
            $transaction->updateThreedsVersion(0);

            /** @var RocketgateBillerResponse $billerResponse */
            $billerResponse = $this->chargeService->chargeNewCreditCard($transaction);

            Log::info(
                'Rocketgate Response retrying with bypassing 3ds',
                [
                    'transactionId'      => (string) $transaction->transactionId(),
                    'billerResponseCode' => $billerResponse->code(),
                    'isNSFSupported'     => $isNSFSupported,
                    'isNSFFlowActive'    => env('NSF_FLOW_ENABLED')
                ]
            );

            $transaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

            // we know it's nfs with 3ds bypass and we have the card details so do card upload
            if ($billerResponse->isNsfTransaction() && env('NSF_FLOW_ENABLED') && $isNSFSupported) {
                Log::info("Retry card upload transaction for nsf with 3ds");
                $newTransaction = clone $transaction;

                // Perform card upload operation
                $billerResponseOnCardUpload = $this->chargeService->cardUpload($newTransaction);
                $newTransaction->updateRocketgateTransactionFromBillerResponse($billerResponseOnCardUpload);
            }

        } else {
            // Because the 3D flag is not stored in the DB, it needs to be set before the transaction
            // is updated with the biller response, or else it will default to false
            $transaction->updateTransactionWith3D(true);

            $transaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

            // write BiLogger event for lookup
            $this->biLoggerService->write(new LookupRequest($billerResponse));
        }

        // We need to write the DWS event update for the following cases:
        // - when we have to retry a transaction without 3DS
        // - when we have a frictionless transaction (if authentication is required then this event will be written on complete)
        if ($shouldRetryWithoutThreeD || !$billerResponse->threeDsAuthIsRequired()) {
            $this->biLoggerService->write(
                new TransactionUpdated(
                    $transaction,
                    $billerResponse,
                    RocketGateBillerSettings::ROCKETGATE,
                    BillerSettings::ACTION_UPDATE
                )
            );
        }

        return $transaction;
    }
}
