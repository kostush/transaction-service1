<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\UnitTestCase;

class PumapayRebillPostbackHttpCommandDTOAssemblerTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function it_should_return_pumapay_rebill_postback_dto_command(): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $assembler       = new PumapayRebillPostbackHttpCommandDTOAssembler();

        $this->assertInstanceOf(PumapayRebillPostbackCommandHttpDTO::class, $assembler->assemble($transactionMock));
    }
}
