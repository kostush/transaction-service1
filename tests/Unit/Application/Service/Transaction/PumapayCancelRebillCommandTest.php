<?php

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\PumapayCancelRebillCommand;
use Tests\UnitTestCase;

class PumapayCancelRebillCommandTest extends UnitTestCase
{
    /**
     * @test
     * @return PumapayCancelRebillCommand
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_create_a_pumapay_cancel_rebill_command(): PumapayCancelRebillCommand
    {
        $command = new PumapayCancelRebillCommand(
            '622ff331-5f5c-33df-ac20-71f426e59131',
            'businessId',
            'businessModel',
            'apiKey'
        );

        $this->assertInstanceOf(PumapayCancelRebillCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @depends it_should_create_a_pumapay_cancel_rebill_command
     * @param PumapayCancelRebillCommand $command The command
     * @return void
     */
    public function it_should_contain_a_transaction_id(PumapayCancelRebillCommand $command): void
    {
        $this->assertSame('622ff331-5f5c-33df-ac20-71f426e59131', $command->transactionId());
    }

    /**
     * @test
     * @depends it_should_create_a_pumapay_cancel_rebill_command
     * @param PumapayCancelRebillCommand $command The command
     * @return void
     */
    public function it_should_contain_a_business_id(PumapayCancelRebillCommand $command): void
    {
        $this->assertSame('businessId', $command->businessId());
    }

    /**
     * @test
     * @depends it_should_create_a_pumapay_cancel_rebill_command
     * @param PumapayCancelRebillCommand $command The command
     * @return void
     */
    public function it_should_contain_a_business_model(PumapayCancelRebillCommand $command): void
    {
        $this->assertSame('businessModel', $command->businessModel());
    }

    /**
     * @test
     * @depends it_should_create_a_pumapay_cancel_rebill_command
     * @param PumapayCancelRebillCommand $command The command
     * @return void
     */
    public function it_should_contain_an_api_key(PumapayCancelRebillCommand $command): void
    {
        $this->assertSame('apiKey', $command->apiKey());
    }
}
