<?php
declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ChargeTransactionQueryHttpDTO;
use ProBillerNG\Transaction\Application\DTO\HttpQueryDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\RebillUpdateTransactionQueryHttpDTO;
use Tests\UnitTestCase;

class HttpQueryDTOAssemblerTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_return_charge_transaction_query_when_charge_transaction_is_provided(): void
    {
        $httpQueryDTOAssembler = new HttpQueryDTOAssembler();
        $transaction           = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->assertInstanceOf(
            ChargeTransactionQueryHttpDTO::class,
            $httpQueryDTOAssembler->assemble($transaction)
        );
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_return_rebill_update_transaction_query_when_rebill_update_transaction_is_provided(): void
    {
        $httpQueryDTOAssembler = new HttpQueryDTOAssembler();
        $transaction           = $this->createUpdateRebillTransaction();

        $this->assertInstanceOf(
            RebillUpdateTransactionQueryHttpDTO::class,
            $httpQueryDTOAssembler->assemble($transaction)
        );
    }
}
