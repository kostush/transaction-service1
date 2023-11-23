<?php

declare(strict_types=1);

namespace Tests\Unit\Infastructure\Domain\Model;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayRetrieveQrCodeBillerResponse;
use Tests\UnitTestCase;

/**
 * Class PumapayRetrieveQrCodeBillerResponseTest
 * @package Tests\Unit\Infastructure\Domain\Model
 */
class PumapayRetrieveQrCodeBillerResponseTest extends UnitTestCase
{
    /**
     * @test
     */
    public function it_should_return_qrCode_and_encryptText_empty_if_biller_response_has_errors()
    {
        $responsePayload = '{"code":102,"reason":"Bad request received from Pumapay servers - Info: [\"currency\" must be one of [USD, JPY, EUR, GBP, PMA, null]]","request":{"currency":"RON","title":"Brazzers Membership","description":"1$ day then daily rebill at 1$ for 3 days","frequency":null,"trialPeriod":null,"numberOfPayments":null,"typeID":2,"amount":100,"initialPaymentAmount":null},"response":{"success":false,"status":400,"error":[{"message":"\"currency\" must be one of [USD, JPY, EUR, GBP, PMA, null]","path":["currency"],"type":"any.allowOnly","context":{"value":"RON","valids":["USD","JPY","EUR","GBP","PMA",null],"key":"currency","label":"currency"}}]}}';

        $result = PumapayRetrieveQrCodeBillerResponse::create(new \DateTimeImmutable(), $responsePayload, new \DateTimeImmutable());

        $this->assertEmpty($result->qrCode());
        $this->assertEmpty($result->encryptText());
    }
}
