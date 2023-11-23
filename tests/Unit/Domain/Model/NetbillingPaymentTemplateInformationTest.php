<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\NetbillingCardHash;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use Tests\UnitTestCase;

class NetbillingPaymentTemplateInformationTest extends UnitTestCase
{
    /**
     * @test
     * @return array
     */
    public function it_should_return_a_payment_template_information_object(): array
    {
        $netbillingCardHash = $this->createMock(NetbillingCardHash::class);
        $paymentTemplate    = NetbillingPaymentTemplateInformation::create($netbillingCardHash);

        $this->assertInstanceOf(NetbillingPaymentTemplateInformation::class, $paymentTemplate);
        return [$netbillingCardHash, $paymentTemplate];
    }

    /**
     * @test
     * @param array $data The data array
     * @depends it_should_return_a_payment_template_information_object
     * @return void
     */
    public function it_should_contain_the_correct_object(array $data): void
    {
        list($netbillingCardHash, $paymentTemplate) = $data;
        $this->assertSame($netbillingCardHash, $paymentTemplate->netbillingCardHash());
    }

    /**
     *
     * @test
     * @return void
     */
    public function it_should_throw_exception_for_invalid_argument(): void
    {
        $this->expectException(\TypeError::class);
        NetbillingPaymentTemplateInformation::create('invalid');
    }
}
