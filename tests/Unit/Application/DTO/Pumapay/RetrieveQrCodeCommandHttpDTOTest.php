<?php

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrieveQrCodeCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\UnitTestCase;

class RetrieveQrCodeCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return RetrieveQrCodeCommandHttpDTO
     */
    public function it_should_have_the_status_key_when_json_serialize_is_called(): RetrieveQrCodeCommandHttpDTO
    {
        $transactionMock = $this->createMock(Transaction::class);
        $statusMock      = $this->createMock(Pending::class);
        $statusMock->method('pending')->willReturn(true);
        $transactionMock->method('status')->willReturn($statusMock);

        $dto = new RetrieveQrCodeCommandHttpDTO($transactionMock, 'qrCode', 'encryptText');

        $this->assertArrayHasKey('status', $dto->jsonSerialize());

        return $dto;
    }

    /**
     * @test
     * @param RetrieveQrCodeCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called
     */
    public function it_should_have_the_transaction_id_key_when_status_is_pending(
        RetrieveQrCodeCommandHttpDTO $dto
    ): void {
        $this->assertArrayHasKey('transactionId', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param RetrieveQrCodeCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called
     */
    public function it_should_have_the_qr_code_key_when_status_is_pending(RetrieveQrCodeCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('qrCode', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param RetrieveQrCodeCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_have_the_status_key_when_json_serialize_is_called
     */
    public function it_should_have_the_encrypt_text_key_when_status_is_pending(RetrieveQrCodeCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('encryptText', $dto->jsonSerialize());
    }

    /**
     * @test
     * @return RetrieveQrCodeCommandHttpDTO
     */
    public function it_should_not_have_the_qr_code_key_when_status_is_aborted(): RetrieveQrCodeCommandHttpDTO
    {
        $transactionMock = $this->createMock(Transaction::class);
        $statusMock      = $this->createMock(Aborted::class);
        $statusMock->method('pending')->willReturn(false);
        $transactionMock->method('status')->willReturn($statusMock);

        $dto = new RetrieveQrCodeCommandHttpDTO($transactionMock, 'qrCode', 'encryptText');

        $this->assertArrayNotHasKey('qrCode', $dto->jsonSerialize());

        return $dto;
    }

    /**
     * @test
     * @return RetrieveQrCodeCommandHttpDTO
     */
    public function it_should_not_have_the_encrypt_text_key_when_status_is_aborted(): RetrieveQrCodeCommandHttpDTO
    {
        $transactionMock = $this->createMock(Transaction::class);
        $statusMock      = $this->createMock(Aborted::class);
        $statusMock->method('pending')->willReturn(false);
        $transactionMock->method('status')->willReturn($statusMock);

        $dto = new RetrieveQrCodeCommandHttpDTO($transactionMock, 'qrCode', 'encryptText');

        $this->assertArrayNotHasKey('encryptText', $dto->jsonSerialize());

        return $dto;
    }

    /**
     * @test
     * @param RetrieveQrCodeCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_not_have_the_qr_code_key_when_status_is_aborted
     */
    public function it_should_have_the_code_key_when_status_is_aborted(RetrieveQrCodeCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('code', $dto->jsonSerialize());
    }

    /**
     * @test
     * @param RetrieveQrCodeCommandHttpDTO $dto DTO
     * @return void
     * @depends it_should_not_have_the_qr_code_key_when_status_is_aborted
     */
    public function it_should_have_the_error_key_when_status_is_aborted(RetrieveQrCodeCommandHttpDTO $dto): void
    {
        $this->assertArrayHasKey('error', $dto->jsonSerialize());
    }
}
