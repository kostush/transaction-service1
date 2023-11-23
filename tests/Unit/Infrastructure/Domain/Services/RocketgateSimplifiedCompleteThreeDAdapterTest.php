<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use DateTime;
use DateTimeImmutable;
use Exception;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSimplifiedCompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\ChargeClient;
use Prophecy\Argument;
use Tests\UnitTestCase;
use TypeError;

class RocketgateSimplifiedCompleteThreeDAdapterTest extends UnitTestCase
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
     * @var SimplifiedCompleteThreeDCommand
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
        $this->mockCommand        = $this->prophesize(SimplifiedCompleteThreeDCommand::class);
        $this->mockBillerResponse = $this->prophesize(RocketgateCreditCardBillerResponse::class);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_call_the_client_and_the_translator_and_return_a_rocketgate_biller_response(): void
    {
        $this->mockClient->simplifiedCompleteThreeD($this->mockCommand->reveal())->shouldBeCalled();
        $this->mockClient->simplifiedCompleteThreeD($this->mockCommand->reveal())->willReturn('json');

        $this->mockTranslator->toCreditCardBillerResponse(
            'json',
            Argument::type(DateTimeImmutable::class),
            Argument::type(DateTimeImmutable::class)
        )->shouldBeCalled()->willReturn($this->mockBillerResponse->reveal());

        $adapter = new RocketgateSimplifiedCompleteThreeDAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );

        $adapter->simplifiedComplete($this->mockCommand->reveal(), new DateTimeImmutable());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_throw_exception_if_incorrect_command_argument_is_passed(): void
    {
        $this->expectException(TypeError::class);

        $adapter = new RocketgateSimplifiedCompleteThreeDAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->simplifiedComplete('invalid', new DateTimeImmutable());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_throw_exception_if_incorrect_date_time_argument_is_passed(): void
    {
        $this->expectException(TypeError::class);

        $adapter = new RocketgateSimplifiedCompleteThreeDAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->simplifiedComplete($this->mockCommand, new DateTime());
    }
}
