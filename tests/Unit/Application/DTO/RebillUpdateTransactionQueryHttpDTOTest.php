<?php
declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\RebillUpdateTransactionQueryHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrievePumapayRebillUpdateTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveRebillUpdateTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use Tests\CreatesTransactionData;
use Tests\UnitTestCase;

class RebillUpdateTransactionQueryHttpDTOTest extends UnitTestCase
{
    use CreatesTransactionData;

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function transaction_payload_should_contain_retrieve_pumapay_rebill_update_transaction_return_type_object(): void
    {
        $previousTransaction = $this->createChargeTransactionWithoutRebillOnPumapay();

        $transaction = RebillUpdateTransaction::createPumapayRebillUpdateTransaction($previousTransaction);

        $httpQueryDTOAssembler = new RebillUpdateTransactionQueryHttpDTO($transaction);

        $reflection = new \ReflectionClass($httpQueryDTOAssembler);
        $prop       = $reflection->getProperty('transactionPayload');
        $prop->setAccessible(true);
        $transactionPayload = $prop->getValue($httpQueryDTOAssembler);

        $this->assertInstanceOf(
            RetrievePumapayRebillUpdateTransactionReturnType::class,
            $transactionPayload
        );
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function transaction_payload_should_contain_retrieve_rebill_update_transaction_return_type_object(): void
    {
        $transaction = $this->createUpdateRebillTransaction();

        $httpQueryDTOAssembler = new RebillUpdateTransactionQueryHttpDTO($transaction);

        $reflection = new \ReflectionClass($httpQueryDTOAssembler);
        $prop       = $reflection->getProperty('transactionPayload');
        $prop->setAccessible(true);
        $transactionPayload = $prop->getValue($httpQueryDTOAssembler);

        $this->assertInstanceOf(
            RetrieveRebillUpdateTransactionReturnType::class,
            $transactionPayload
        );
    }
}
