<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Domain\Services;

use ProBillerNG\Pumapay\Application\Services\GenerateQrCodeCommandHandler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayRetrieveQrCodeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslator;
use Tests\IntegrationTestCase;

/**
 * Class PumapayRetrieveQrCodeAdapterTest
 * @package Tests\Integration\Infrastructure\Domain\Services
 */
class PumapayRetrieveQrCodeAdapterTest extends IntegrationTestCase
{
    /**
     * @test
     */
    public function it_should_handle_exception_when_invalid_biller_response_is_received()
    {
        $this->expectException(InvalidBillerResponseException::class);

        $qrCommandHandler = $this->createMock(GenerateQrCodeCommandHandler::class);
        $qrCommandHandler->method('execute')->willReturn(
            json_encode(
                [
                    'code'   => '530',
                    'reason' => 'Server error: `GET https://psp-backend.pumapay.io/api/v2/api-key-auth/qr/pull-payment/BHFJ5epZgSHTihLrgsZpYSJkFjqKb3JC/im5oXFrmXRzpekY2vl8m2C6AR2e12H1A/6b1f4794-aa13-4680-b839-49c6ccc49d02?currency=USD&title=PornhubPremium_9.99_30_9.99_30_Crypto&description=Membership%20to%20pornhubpremium.com%20for%2030%20days%20for%20a%20charge%20of%20%249.99&frequency=2592000&trialPeriod=2592000&numberOfPayments=60&typeID=6&amount=999&initialPaymentAmount=999` resulted in a `530 ` response'
                ]
            )
        );

        $adaptor = new PumapayRetrieveQrCodeAdapter(
            $qrCommandHandler,
            new PumapayTranslator()
        );

        $transaction = ChargeTransaction::createSingleChargeOnPumapay(
            $this->faker->uuid,
            1.0,
            PumaPayBillerSettings::PUMAPAY,
            'EUR',
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            null
        );

        $adaptor->retrieveQrCode($transaction);
    }

}
