<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSuspendRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\ChargeClient;
use Prophecy\Argument;
use Tests\UnitTestCase;

class RocketGateSuspendRebillAdapterTest extends UnitTestCase
{
    /**
     * @var ChargeClient
     */
    private $mockClient;

    /**
     * @var RocketgateCreditCardChargeTranslator
     */
    private $mockTranslator;

    /**
     * @var SuspendRebillCommand
     */
    private $mockCommand;

    /**
     * @var RocketgateCreditCardBillerResponse
     */
    private $mockBillerResponse;

    /**
     * setup method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->mockClient         = $this->prophesize(ChargeClient::class);
        $this->mockTranslator     = $this->prophesize(RocketgateCreditCardChargeTranslator::class);
        $this->mockCommand        = $this->prophesize(SuspendRebillCommand::class);
        $this->mockBillerResponse = $this->prophesize(RocketgateCreditCardBillerResponse::class);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_call_the_client_and_the_translator_and_return_a_rocketgate_biller_response(): void
    {
        $this->mockClient->suspendRebill($this->mockCommand->reveal())->shouldBeCalled();
        $this->mockClient->suspendRebill($this->mockCommand->reveal())->willReturn('json');

        $this->mockTranslator->toCreditCardBillerResponse(
            'json',
            Argument::type(\DateTimeImmutable::class),
            Argument::type(\DateTimeImmutable::class)
        )->shouldBeCalled()->willReturn($this->mockBillerResponse->reveal());

        $adapter = new RocketgateSuspendRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );

        $adapter->suspend($this->mockCommand->reveal(), new \DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_if_incorrect_command_argument_is_passed(): void
    {
        $this->expectException(\TypeError::class);

        $adapter = new RocketgateSuspendRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->suspend('invalid', new \DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_if_incorrect_date_time_argument_is_passed(): void
    {
        $this->expectException(\TypeError::class);

        $adapter = new RocketgateSuspendRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->suspend($this->mockCommand, new \DateTime());
    }
}
