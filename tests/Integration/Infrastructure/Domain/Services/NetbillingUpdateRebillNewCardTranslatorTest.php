<?php
declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Exception;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingNewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillNewCardTranslator;
use Tests\CreateTransactionDataForNetbilling;
use Tests\IntegrationTestCase;

class NetbillingUpdateRebillNewCardTranslatorTest extends IntegrationTestCase
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
     * @throws LoggerException
     * @throws Exception
     */
    public function it_should_call_charge_and_update_for_approved_charge():void
    {
        $this->billerResponse->method('result')->willReturn(NetbillingBillerResponse::CHARGE_RESULT_APPROVED);
        $this->billerResponse->method('reason')->willReturn('approved');

        /** @var NetbillingNewCreditCardChargeAdapter|MockObject $existingCardChargeAdapter */
        $existingCardChargeAdapter = $this->createMock(NetbillingNewCreditCardChargeAdapter::class);
        $existingCardChargeAdapter
            ->expects($this->once())
            ->method('charge')
            ->willReturn($this->billerResponse);

        $updateRebillAdapter = $this->createMock(NetbillingUpdateRebillAdapter::class);
        $updateRebillAdapter
            ->expects($this->once())
            ->method('update')
            ->willReturn($this->billerResponse);

        $netbillingUpdateRebillExistingCardTranslator = new NetbillingUpdateRebillNewCardTranslator(
            $existingCardChargeAdapter,
            $updateRebillAdapter
        );
        $netbillingUpdateRebillExistingCardTranslator->update($this->createUpdateRebillNewCardNetbillingTransaction());
    }

    /**
     * @test
     * @return void
     * @throws LoggerException
     * @throws Exception
     */
    public function it_should_not_update_when_the_charge_is_declined():void
    {
        $this->billerResponse->method('result')->willReturn(NetbillingBillerResponse::CHARGE_RESULT_DECLINED);

        $newCardChargeAdapter = $this->createMock(NetbillingNewCreditCardChargeAdapter::class);
        $newCardChargeAdapter
            ->expects($this->once())
            ->method('charge')
            ->willReturn($this->billerResponse);

        $updateRebillAdapter = $this->createMock(NetbillingUpdateRebillAdapter::class);
        $updateRebillAdapter->expects($this->never())->method('update');

        $netbillingUpdateRebillNewCardTranslator = new NetbillingUpdateRebillNewCardTranslator(
            $newCardChargeAdapter,
            $updateRebillAdapter
        );
        $netbillingUpdateRebillNewCardTranslator->update($this->createUpdateRebillNewCardNetbillingTransaction());
    }
}
