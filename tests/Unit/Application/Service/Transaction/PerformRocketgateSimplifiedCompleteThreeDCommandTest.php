<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use Exception;
use InvalidArgumentException;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateSimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

class PerformRocketgateSimplifiedCompleteThreeDCommandTest extends UnitTestCase
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $queryString;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionId = 'f32cd429-f41c-49dd-bee7-7fd767b91615';
        $this->queryString   = 'id=3DS-Simplified&invoiceID=1632830712&hash=FvsHjDA%2FJqQ7o9TV3baCSiWCX5E%3D';
    }

    /**
     * @test
     * @return PerformRocketgateSimplifiedCompleteThreeDCommand
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_return_simplified_complete_threeD_command_instance(): PerformRocketgateSimplifiedCompleteThreeDCommand
    {
        $command = new PerformRocketgateSimplifiedCompleteThreeDCommand(
            $this->transactionId,
            $this->queryString
        );

        self::assertInstanceOf(PerformRocketgateSimplifiedCompleteThreeDCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @param PerformRocketgateSimplifiedCompleteThreeDCommand $command Complete ThreeD Command
     * @depends it_should_return_simplified_complete_threeD_command_instance
     * @return void
     */
    public function it_should_return_correct_transaction_id(PerformRocketgateSimplifiedCompleteThreeDCommand $command): void
    {
        self::assertSame($this->transactionId, $command->transactionId());
    }

    /**
     * @test
     * @param PerformRocketgateSimplifiedCompleteThreeDCommand $command Complete ThreeD Command
     * @depends it_should_return_simplified_complete_threeD_command_instance
     * @return void
     */
    public function it_should_return_correct_query_string(PerformRocketgateSimplifiedCompleteThreeDCommand $command): void
    {
        self::assertSame($this->queryString, $command->queryString());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function it_should_thrown_missing_charge_information_exception_if_query_string_is_null(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new PerformRocketgateSimplifiedCompleteThreeDCommand(
            $this->transactionId,
            ''
        );
    }
}
