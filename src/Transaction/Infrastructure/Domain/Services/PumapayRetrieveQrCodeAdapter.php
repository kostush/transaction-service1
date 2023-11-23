<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Pumapay\Application\Services\GenerateQrCodeCommand;
use ProBillerNG\Pumapay\Application\Services\GenerateQrCodeCommandHandler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Services\PumapayRetrieveQrCodeAdapter as PumapayAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;

/**
 * Class PumapayRetrieveQrCodeAdapter
 * @package ProBillerNG\Transaction\Infrastructure\Domain\Services
 */
class PumapayRetrieveQrCodeAdapter implements PumapayAdapterInterface
{
    /**
     * @var GenerateQrCodeCommandHandler
     */
    private $generateQrCodeCommandHandler;

    /**
     * @var PumapayTranslator
     */
    protected $translator;

    /**
     * PumapayRetrieveQrCodeAdapter constructor.
     * @param GenerateQrCodeCommandHandler $generateQrCodeCommandHandler
     * @param PumapayTranslator            $translator Translator
     */
    public function __construct(
        GenerateQrCodeCommandHandler $generateQrCodeCommandHandler,
        PumapayTranslator $translator
    ) {
        $this->generateQrCodeCommandHandler = $generateQrCodeCommandHandler;
        $this->translator                   = $translator;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     *
     * @return PumapayBillerResponse
     * @throws InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function retrieveQrCode(ChargeTransaction $transaction): PumapayBillerResponse
    {
        Log::info('Send Pumapay retrieve QR code request');

        $qrCommand = new GenerateQrCodeCommand(
            $transaction->billerChargeSettings()->apiKey(),
            $transaction->billerChargeSettings()->businessModel(),
            $transaction->billerChargeSettings()->businessId(),
            (string) $transaction->transactionId(),
            $transaction->billerChargeSettings()->title(),
            $transaction->billerChargeSettings()->description(),
            (string) $transaction->chargeInformation()->currency(),
            $transaction->chargeInformation()->amount()->value(),
            $transaction->chargeInformation()->rebill() ? $transaction->chargeInformation()->rebill()->amount()->value()
                : null,
            $transaction->chargeInformation()->rebill() ? $transaction->chargeInformation()->rebill()->frequency()
                : null,
            $transaction->chargeInformation()->rebill() ? $transaction->chargeInformation()->rebill()->start() : null,
            // The following flag is turned false, because Pumapay pre-prod is very unstable, also our system tests are
            // using production api key, so some tests were failing.
            // Once we start relying on Pumapay pre-prod env, we have to switch back this flag and update the api key.
            env('BILLER_PUMAPAY_TEST_MODE', false)
        );

        $requestDate = new \DateTimeImmutable();
        $response    = $this->generateQrCodeCommandHandler->execute($qrCommand);

        // Call the translator
        return $this->translator->toRetrieveQrCodeBillerResponse(
            $response,
            $requestDate,
            new \DateTimeImmutable()
        );
    }
}
