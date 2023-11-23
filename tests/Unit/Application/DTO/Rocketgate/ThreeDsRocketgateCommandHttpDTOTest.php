<?php

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\UnitTestCase;

class ThreeDsRocketgateCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return TransactionCommandHttpDTO
     */
    public function it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed(): TransactionCommandHttpDTO
    {
        $transactionMock = $this->createMock(Transaction::class);
        $statusMock      = $this->createMock(Pending::class);

        $statusMock->method('pending')->willReturn(true);
        $transactionMock->method('status')->willReturn($statusMock);
        $transactionMock->method('threedsVersion')->willReturn(1);
        $transactionMock->method('with3D')->willReturn(true);
        $transactionMock->method('responsePayload')->willReturn(
            json_encode(
                [
                    'PAREQ'  => 'pareq',
                    'acsURL' => 'url'
                ]
            )
        );

        $dto = new TransactionCommandHttpDTO($transactionMock);

        $this->assertArrayHasKey('status', $dto->jsonSerialize());

        return $dto;
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed
     */
    public function it_should_have_the_transaction_id_key_when_status_is_pending_on_threeds_one(
        TransactionCommandHttpDTO $dto
    ): void {
        $this->assertArrayHasKey('transactionId', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed
     */
    public function it_should_have_the_pareq_key_when_status_is_pending(TransactionCommandHttpDTO $dto): void
    {
        // TODO: will be deprecated as soon as the merge with the latest response code is made
        $this->assertArrayHasKey('pareq', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed
     */
    public function it_should_have_the_acs_key_when_status_is_pending(TransactionCommandHttpDTO $dto): void
    {
        // TODO: will be deprecated as soon as the merge with the latest response code is made
        $this->assertArrayHasKey('acs', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed
     */
    public function it_should_have_the_threed_key_when_status_is_pending_on_threeds_one(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('threeD', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed
     */
    public function it_should_have_the_pareq_key_when_status_is_pending_on_threeds_one(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('pareq', $dto->jsonSerialize()['threeD']);
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed
     */
    public function it_should_have_the_acs_key_when_status_is_pending_on_threeds_one(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('acs', $dto->jsonSerialize()['threeD']);
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_one_needed
     */
    public function it_should_have_the_version_key_when_status_is_pending_on_threeds_one(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('version', $dto->jsonSerialize()['threeD']);
    }

    /**
     * @test
     * @return TransactionCommandHttpDTO
     */
    public function it_should_have_the_status_key_when_json_serialize_is_called_when_threed_two_needed(): TransactionCommandHttpDTO
    {
        $transactionMock = $this->createMock(Transaction::class);
        $statusMock      = $this->createMock(Pending::class);

        $statusMock->method('pending')->willReturn(true);
        $transactionMock->method('status')->willReturn($statusMock);
        $transactionMock->method('threedsVersion')->willReturn(2);
        $transactionMock->method('with3D')->willReturn(true);
        $transactionMock->method('responsePayloadThreeDsTwo')->willReturn(
            json_encode(
                [
                    '_3DSECURE_STEP_UP_URL' => 'url',
                    '_3DSECURE_STEP_UP_JWT' => 'jwt',
                    'guidNo'                => 'billerTransactionId',
                ]
            )
        );

        $dto = new TransactionCommandHttpDTO($transactionMock);

        $this->assertArrayHasKey('status', $dto->jsonSerialize());

        return $dto;
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_two_needed
     */
    public function it_should_have_the_transaction_id_key_when_status_is_pending_on_threeds_two(
        TransactionCommandHttpDTO $dto
    ): void {
        $this->assertArrayHasKey('transactionId', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_two_needed
     */
    public function it_should_have_the_threed_key_when_status_is_pending_on_threeds_two(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('threeD', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_two_needed
     */
    public function it_should_have_the_step_up_url_key_when_status_is_pending_on_threeds_two(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('stepUpUrl', $dto->jsonSerialize()['threeD']);
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_two_needed
     */
    public function it_should_have_the_step_up_jwt_key_when_status_is_pending_on_threeds_two(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('stepUpJwt', $dto->jsonSerialize()['threeD']);
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_two_needed
     */
    public function it_should_have_the_md_key_when_status_is_pending_on_threeds_two(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('md', $dto->jsonSerialize()['threeD']);
    }

    /**
     * @test
     * @param TransactionCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called_when_threed_two_needed
     */
    public function it_should_have_the_version_key_when_status_is_pending_on_threeds_two(TransactionCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('version', $dto->jsonSerialize()['threeD']);
    }
}
