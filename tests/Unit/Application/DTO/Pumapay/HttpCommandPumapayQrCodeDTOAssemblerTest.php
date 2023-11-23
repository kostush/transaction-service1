<?php

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayQrCodeHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrieveQrCodeCommandHttpDTO;
use Tests\UnitTestCase;

class PumapayQrCodeHttpCommandDTOAssemblerTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_return_retrieve_qr_code_command_when_charge_transaction_is_provided(): void
    {
        $dtoAssembler = new PumapayQrCodeHttpCommandDTOAssembler();
        $transaction  = $this->createChargeTransactionWithoutRebillOnPumapay();

        $this->assertInstanceOf(
            RetrieveQrCodeCommandHttpDTO::class,
            $dtoAssembler->assemble($transaction, 'qrCode', 'encryptText')
        );
    }
}
