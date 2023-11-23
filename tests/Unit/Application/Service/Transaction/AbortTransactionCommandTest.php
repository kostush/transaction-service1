<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\AbortTransactionCommand;
use Tests\UnitTestCase;

class AbortTransactionCommandTest extends UnitTestCase
{
    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return AbortTransactionCommand
     */
    public function it_create_should_return_command_instance(): AbortTransactionCommand
    {
        $command = new AbortTransactionCommand('uuid-string');

        $this->assertInstanceOf(AbortTransactionCommand::class, $command);

        return $command;
    }

    /**
     * @test
     * @depends it_create_should_return_command_instance
     * @param AbortTransactionCommand $command AbortTransactionCommand
     * @return void
     */
    public function it_should_return_transaction_id_uuid(AbortTransactionCommand $command): void
    {
        $this->assertSame('uuid-string', $command->transactionId());
    }
}
