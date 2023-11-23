<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use ProBillerNG\Pumapay\Application\Services\GenerateQrCodeCommandHandler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayRetrieveQrCodeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslator;
use Tests\UnitTestCase;

class PumapayRetrieveQrCodeAdapterTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Exception
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException
     */
    public function it_should_return_a_json_when_execute_method_is_called(): void
    {
        $commandHandler = $this->createMock(GenerateQrCodeCommandHandler::class);
        $transaction    = ChargeTransaction::createSingleChargeOnPumapay(
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

        $commandHandler->method('execute')->willReturn(
            '{ 
               "request":{ 
                  "currency":"EUR",
                  "title":"Brazzers Membership",
                  "description":"1$ day then daily rebill at 1$ for 3 days",
                  "frequency":null,
                  "trialPeriod":null,
                  "numberOfPayments":null,
                  "typeID":2,
                  "amount":100,
                  "initialPaymentAmount":null
               },
               "response":{ 
                  "success":true,
                  "status":200,
                  "message":"Successfully retrieved the QR code.",
                  "data":{
                     "qrImage": "qrCode",
                     "encryptText": "encryptText"
                  }
               },
               "code":200,
               "reason":null
            }'
        );

        $adapter = new PumapayRetrieveQrCodeAdapter(
            $commandHandler,
            new PumapayTranslator()
        );

        $result = $adapter->retrieveQrCode($transaction);

        $this->assertInstanceOf(PumapayBillerResponse::class, $result);
    }
}
