<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\UpdateRebillClient;
use Prophecy\Argument;
use Tests\UnitTestCase;

class RocketGateUpdateRebillAdapterTest extends UnitTestCase
{
    /**
     * @var UpdateRebillClient
     */
    private $mockClient;

    /**
     * @var RocketgateCreditCardChargeTranslator
     */
    private $mockTranslator;

    /**
     * @var UpdateRebillCommand
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
        $this->mockClient         = $this->prophesize(UpdateRebillClient::class);
        $this->mockTranslator     = $this->prophesize(RocketgateCreditCardChargeTranslator::class);
        $this->mockCommand        = $this->prophesize(UpdateRebillCommand::class);
        $this->mockBillerResponse = $this->prophesize(RocketgateCreditCardBillerResponse::class);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function start_should_call_the_client_and_the_translator_and_return_a_rocketgate_biller_response(): void
    {
        $this->mockClient->start($this->mockCommand->reveal())->shouldBeCalled();
        $this->mockClient->start($this->mockCommand->reveal())->willReturn('json');

        $this->mockTranslator->toCreditCardBillerResponse(
            'json',
            Argument::type(\DateTimeImmutable::class),
            Argument::type(\DateTimeImmutable::class)
        )->shouldBeCalled()->willReturn($this->mockBillerResponse->reveal());

        $adapter = new RocketgateUpdateRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );

        $adapter->start($this->mockCommand->reveal(), new \DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function start_should_throw_exception_if_incorrect_command_argument_is_passed(): void
    {
        $this->expectException(\TypeError::class);

        $adapter = new RocketgateUpdateRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->start('invalid', new \DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function start_should_throw_exception_if_incorrect_date_time_argument_is_passed(): void
    {
        $this->expectException(\TypeError::class);

        $adapter = new RocketgateUpdateRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->start($this->mockCommand, new \DateTime());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function stop_should_call_the_client_and_the_translator_and_return_a_rocketgate_biller_response(): void
    {
        $this->mockClient->stop($this->mockCommand->reveal())->shouldBeCalled();
        $this->mockClient->stop($this->mockCommand->reveal())->willReturn('json');

        $this->mockTranslator->toCreditCardBillerResponse(
            'json',
            Argument::type(\DateTimeImmutable::class),
            Argument::type(\DateTimeImmutable::class)
        )->shouldBeCalled()->willReturn($this->mockBillerResponse->reveal());

        $adapter = new RocketgateUpdateRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );

        $adapter->stop($this->mockCommand->reveal(), new \DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function update_should_call_the_client_and_the_translator_and_return_a_rocketgate_biller_response(): void
    {
        $this->mockClient->update($this->mockCommand->reveal())->shouldBeCalled();
        $this->mockClient->update($this->mockCommand->reveal())->willReturn('json');

        $this->mockTranslator->toCreditCardBillerResponse(
            'json',
            Argument::type(\DateTimeImmutable::class),
            Argument::type(\DateTimeImmutable::class)
        )->shouldBeCalled()->willReturn($this->mockBillerResponse->reveal());

        $adapter = new RocketgateUpdateRebillAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );

        $adapter->update($this->mockCommand->reveal(), new \DateTimeImmutable());
    }
}
