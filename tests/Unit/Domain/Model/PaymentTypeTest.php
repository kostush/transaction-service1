<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\BI\BaseEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentTypeException;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class PaymentTypeTest
 * @package Tests\Unit\Domain\Model
 */
class PaymentTypeTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws InvalidPaymentTypeException
     * @throws Exception
     */
    public function it_should_not_create_payment_type_for_non_existent_type(): void
    {
        $this->expectException(InvalidPaymentTypeException::class);
        PaymentType::create('inexistentType');
    }

    /**
     * @test
     * @return void
     * @throws InvalidPaymentTypeException
     * @throws Exception
     */
    public function it_should_create_payment_type_for_existent_type(): void
    {
        $paymentType = PaymentType::create(PaymentType::CREDIT_CARD);
        $this->assertInstanceOf(PaymentType::class, $paymentType);
        $this->assertEquals(PaymentType::CREDIT_CARD, $paymentType->getValue());
    }


    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_credit_card(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::CREDIT_CARD, $biMappingArray);
        $this->assertContains(BaseEvent::BI_CREDIT_CARD_TYPE, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_crypto(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::CRYPTO, $biMappingArray);
        $this->assertContains(PaymentType::CRYPTO, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_alipay(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::ALIPAY, $biMappingArray);
        $this->assertContains(PaymentType::ALIPAY, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_elv(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::ELV, $biMappingArray);
        $this->assertContains(PaymentType::ELV, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_mcb(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::MCB, $biMappingArray);
        $this->assertContains(PaymentType::MCB, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_paysafecard(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::PAYSAFECARD, $biMappingArray);
        $this->assertContains(PaymentType::PAYSAFECARD, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_sepa(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::SEPA, $biMappingArray);
        $this->assertContains(PaymentType::SEPA, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_skrill(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::SKRILL, $biMappingArray);
        $this->assertContains(PaymentType::SKRILL, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_sofortbanking(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::SOFORTBANKING, $biMappingArray);
        $this->assertContains(PaymentType::SOFORTBANKING, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_unionpay(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::UNIONPAY, $biMappingArray);
        $this->assertContains(PaymentType::UNIONPAY, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_wechat(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::WECHAT, $biMappingArray);
        $this->assertContains(PaymentType::WECHAT, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_check(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::CHECK, $biMappingArray);
        $this->assertContains(PaymentType::CHECK, $biMappingArray);
    }

    /**
     * @test
     * @return void
     */
    public function bi_payment_mapping_should_have_giftcard(): void
    {
        $biMappingArray = PaymentType::biPaymentTypeMapping();

        $this->assertArrayHasKey(PaymentType::GIFTCARD, $biMappingArray);
        $this->assertContains(PaymentType::GIFTCARD, $biMappingArray);
    }
}
