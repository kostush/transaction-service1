<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\UnitTestCase;

class PumapayJoinPostbackHttpCommandDTOAssemblerTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function it_should_return_pumapay_join_postback_dto_command(): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $assembler       = new PumapayJoinPostbackHttpCommandDTOAssembler();

        $this->assertInstanceOf(PumapayJoinPostbackCommandHttpDTO::class, $assembler->assemble($transactionMock));
    }
}
