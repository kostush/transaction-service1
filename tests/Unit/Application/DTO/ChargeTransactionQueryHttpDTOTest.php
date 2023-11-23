<?php
declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ChargeTransactionQueryHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrievePumapayChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveChargeTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Exception\UnknownBillerNameException;
use Tests\CreatesTransactionData;
use Tests\UnitTestCase;

class ChargeTransactionQueryHttpDTOTest extends UnitTestCase
{
    use CreatesTransactionData;

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function transaction_payload_should_contain_retrieve_pumapay_charge_transaction_return_type_object(): void
    {
        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();

        $httpQueryDTOAssembler = new ChargeTransactionQueryHttpDTO($transaction);

        $reflection = new \ReflectionClass($httpQueryDTOAssembler);
        $prop       = $reflection->getProperty('transactionPayload');
        $prop->setAccessible(true);
        $transactionPayload = $prop->getValue($httpQueryDTOAssembler);

        $this->assertInstanceOf(
            RetrievePumapayChargeTransactionReturnType::class,
            $transactionPayload
        );
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function transaction_payload_should_contain_retrieve_charge_transaction_return_type_object(): void
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge();

        $httpQueryDTOAssembler = new ChargeTransactionQueryHttpDTO($transaction);

        $reflection = new \ReflectionClass($httpQueryDTOAssembler);
        $prop       = $reflection->getProperty('transactionPayload');
        $prop->setAccessible(true);
        $transactionPayload = $prop->getValue($httpQueryDTOAssembler);

        $this->assertInstanceOf(
            RetrieveChargeTransactionReturnType::class,
            $transactionPayload
        );
    }
}
