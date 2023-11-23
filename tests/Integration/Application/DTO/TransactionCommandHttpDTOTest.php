<?php

namespace Tests\Integration\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\DeclinedBillerResponseExtraData;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteria;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\IntegrationTestCase;

class TransactionCommandHttpDTOTest extends IntegrationTestCase
{
    /** @var Transaction */
    protected $transaction;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $status = $this->createMock(Declined::class);
        $status->method('declined')->willReturn(true);
        $status->method('__toString')->willReturn('declined');

        $this->transaction = $this->createMock(Transaction::class);
        $this->transaction->method('status')->willReturn($status);
    }

    /**
     * @test
     */
    public function declined_transaction_should_not_have_error_classification_in_the_output_if_none_given() : void
    {
        $transaction = new TransactionCommandHttpDTO($this->transaction);
        $this->assertArrayNotHasKey('errorClassification', $transaction->jsonSerialize());
    }

    /**
     * @test
     */
    public function declined_transaction_should_have_error_classification_in_the_output_if_one_provided() : void
    {
        $dataMapping = [
            'processor' => 'TEST',
            'authMessage' => 'TEST DECLINED',
            'billerName' => 'Netbilling'
        ];

        // mapping criteria
        $mappingCriteria = $this->createMock(MappingCriteria::class);
        $mappingCriteria->method('toArray')->willReturn($dataMapping);

        // declined biller response
        $declinedBillerResponse = $this->createMock(DeclinedBillerResponseExtraData::class);

        $errorClassification = new ErrorClassification($mappingCriteria, $declinedBillerResponse);

        $transaction = new TransactionCommandHttpDTO($this->transaction, $errorClassification);

        $result = $transaction->jsonSerialize();

        $this->assertArrayHasKey('errorClassification', $result);
        $this->assertEquals($result['errorClassification']['mappingCriteria'], $dataMapping);
    }
}
