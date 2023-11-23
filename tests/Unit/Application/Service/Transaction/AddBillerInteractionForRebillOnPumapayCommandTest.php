<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForRebillOnPumapayCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use Tests\UnitTestCase;

class AddBillerInteractionForRebillOnPumapayCommandTest extends UnitTestCase
{
    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return AddBillerInteractionForRebillOnPumapayCommand
     */
    public function it_create_should_return_command_instance(): AddBillerInteractionForRebillOnPumapayCommand
    {
        $payload = [
            'testKey'        => 'testValue',
            'anotherTestKey' => 'anotherTestValue',
            'arrayKey'       => ['something', 'key' => 'value']
        ];

        $command = new AddBillerInteractionForRebillOnPumapayCommand('uuid-string', $payload);

        $this->assertInstanceOf(AddBillerInteractionForRebillOnPumapayCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @depends it_create_should_return_command_instance
     * @param AddBillerInteractionForRebillOnPumapayCommand $command AddBillerInteractionForRebillOnPumapayCommand
     * @return void
     */
    public function it_should_return_transaction_id_uuid(AddBillerInteractionForRebillOnPumapayCommand $command): void
    {
        $this->assertSame('uuid-string', $command->previousTransactionId());
    }

    /**
     * @test
     * @depends it_create_should_return_command_instance
     * @param AddBillerInteractionForRebillOnPumapayCommand $command
     * @return void
     */
    public function it_should_return_json_payload(AddBillerInteractionForRebillOnPumapayCommand $command): void
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

        new AddBillerInteractionForRebillOnPumapayCommand('uuid-string', []);
    }
}
