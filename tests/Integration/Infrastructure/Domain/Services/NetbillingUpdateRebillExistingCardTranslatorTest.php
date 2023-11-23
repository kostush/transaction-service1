<?php
declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillExistingCardTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\UpdateRebillNetbillingAdapter;
use Tests\CreateTransactionDataForNetbilling;
use Tests\IntegrationTestCase;

class NetbillingUpdateRebillExistingCardTranslatorTest extends IntegrationTestCase
{
    use CreateTransactionDataForNetbilling;

    /** @var MockObject|NetbillingBillerResponse */
    protected $billerResponse;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->billerResponse = $this->createMock(NetbillingBillerResponse::class);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Exception
     */
    public function it_should_call_charge_and_update_for_approved_charge():void
    {
        $this->billerResponse->method('result')->willReturn(NetbillingBillerResponse::CHARGE_RESULT_APPROVED);
        $this->billerResponse->method('reason')->willReturn('approved');

        /** @var NetbillingExistingCreditCardChargeAdapter|MockObject $existingCardChargeAdapter */
        $existingCardChargeAdapter = $this->createMock(NetbillingExistingCreditCardChargeAdapter::class);
        $existingCardChargeAdapter
            ->expects($this->once())
            ->method('charge')
            ->willReturn($this->billerResponse);

        $updateRebillAdapter = $this->createMock(UpdateRebillNetbillingAdapter::class);
        $updateRebillAdapter
            ->expects($this->once())
            ->method('update')
            ->willReturn($this->billerResponse);

        $netbillingUpdateRebillExistingCardTranslator = new NetbillingUpdateRebillExistingCardTranslator(
            $existingCardChargeAdapter,
            $updateRebillAdapter
        );
        $netbillingUpdateRebillExistingCardTranslator->update($this->createUpdateRebillExistingCardNetbillingTransaction());
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Exception
     */
    public function it_should_not_update_when_the_charge_is_declined():void
    {
        $this->billerResponse->method('result')->willReturn(NetbillingBillerResponse::CHARGE_RESULT_DECLINED);

        $existingCardChargeAdapter = $this->createMock(NetbillingExistingCreditCardChargeAdapter::class);
        $existingCardChargeAdapter
            ->expects($this->once())
            ->method('charge')
            ->willReturn($this->billerResponse);

        $updateRebillAdapter = $this->createMock(UpdateRebillNetbillingAdapter::class);
        $updateRebillAdapter->expects($this->never())->method('update');

        $netbillingUpdateRebillExistingCardTranslator = new NetbillingUpdateRebillExistingCardTranslator(
            $existingCardChargeAdapter,
            $updateRebillAdapter
        );
        $netbillingUpdateRebillExistingCardTranslator->update($this->createUpdateRebillExistingCardNetbillingTransaction());
    }
}
