<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO\Qysso;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\UnitTestCase;

class QyssoJoinPostbackHttpCommandDTOAssemblerTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function it_should_return_epoch_join_postback_dto_command(): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $assembler       = new QyssoJoinPostbackHttpCommandDTOAssembler();

        $this->assertInstanceOf(
            QyssoJoinPostbackCommandHttpDTO::class,
            $assembler->assemble($transactionMock)
        );
    }
}
