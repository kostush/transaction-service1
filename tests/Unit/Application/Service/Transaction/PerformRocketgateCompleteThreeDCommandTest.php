<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCompleteThreeDCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

class PerformRocketgateCompleteThreeDCommandTest extends UnitTestCase
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $pares;

    /**
     * @var string
     */
    private $md;

    /**
     * @throws \Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionId = 'f32cd429-f41c-49dd-bee7-7fd767b91615';
        $this->pares         = 'SimulatedPARES10001000E00B000';
        $this->md            = '10001732904A027';
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return PerformRocketgateCompleteThreeDCommand
     */
    public function it_should_return_complete_threeD_command_instance(): PerformRocketgateCompleteThreeDCommand
    {
        $command = new PerformRocketgateCompleteThreeDCommand(
            $this->transactionId,
            $this->pares,
            $this->md
        );

        $this->assertInstanceOf(PerformRocketgateCompleteThreeDCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @param PerformRocketgateCompleteThreeDCommand $command Complete ThreeD Command
     * @depends it_should_return_complete_threeD_command_instance
     * @return void
     */
    public function it_should_return_correct_transaction_id(PerformRocketgateCompleteThreeDCommand $command): void
    {
        $this->assertSame($this->transactionId, $command->transactionId());
    }

    /**
     * @test
     * @param PerformRocketgateCompleteThreeDCommand $command Complete ThreeD Command
     * @depends it_should_return_complete_threeD_command_instance
     * @return void
     */
    public function it_should_return_correct_pares(PerformRocketgateCompleteThreeDCommand $command): void
    {
        $this->assertSame($this->pares, $command->pares());
    }

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function it_should_thrown_missing_charge_information_exception_if_both_pares_and_md_are_null(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new PerformRocketgateCompleteThreeDCommand(
            $this->transactionId,
            '',
            ''
        );
    }
}
