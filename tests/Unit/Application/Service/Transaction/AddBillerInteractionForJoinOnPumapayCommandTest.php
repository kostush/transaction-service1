<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnPumapayCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use Tests\UnitTestCase;

class AddBillerInteractionForJoinOnPumapayCommandTest extends UnitTestCase
{
    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return AddBillerInteractionForJoinOnPumapayCommand
     */
    public function it_create_should_return_command_instance(): AddBillerInteractionForJoinOnPumapayCommand
    {
        $payloadArr = [
            'testKey'        => 'testValue',
            'anotherTestKey' => 'anotherTestValue',
            'arrayKey'       => ['something', 'key' => 'value']
        ];

        $command = new AddBillerInteractionForJoinOnPumapayCommand('uuid-string', $payloadArr);

        $this->assertInstanceOf(AddBillerInteractionForJoinOnPumapayCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @depends it_create_should_return_command_instance
     * @param AddBillerInteractionForJoinOnPumapayCommand $command AddBillerInteractionForJoinOnPumapayCommand
     * @return void
     */
    public function it_should_return_transaction_id_uuid(AddBillerInteractionForJoinOnPumapayCommand $command): void
    {
        $this->assertSame('uuid-string', $command->transactionId());
    }

    /**
     * @test
     * @depends it_create_should_return_command_instance
     * @param AddBillerInteractionForJoinOnPumapayCommand $command AddBillerInteractionForJoinOnPumapayCommand
     * @return void
     */
    public function it_should_return_json_payload(AddBillerInteractionForJoinOnPumapayCommand $command): void
    {
        $this->assertIsArray($command->payload());
    }

    /**
     * @test
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException
     * @return void
     */
    public function it_should_throw_exception_when_payload_is_empty(): void
    {
        $this->expectException(MissingTransactionInformationException::class);

        $payloadArr = [];
        $command    = new AddBillerInteractionForJoinOnPumapayCommand('uuid-string', $payloadArr);
    }
}
