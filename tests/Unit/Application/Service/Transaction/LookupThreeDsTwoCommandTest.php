<?php
declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\LookupThreeDsTwoCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

class LookupThreeDsTwoCommandTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_device_fingerprint_id_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new LookupThreeDsTwoCommand(
            '',
            $this->faker->uuid,
            $this->faker->url,
            $this->createMock(Payment::class)
        );
    }
    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_previous_transaction_id_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new LookupThreeDsTwoCommand(
            '12345',
            '',
            $this->faker->url,
            $this->createMock(Payment::class)
        );
    }
    /**
     * @test
     * @throws \Throwable
     * @return void
     */
    public function it_should_throw_exception_when_return_url_is_missing(): void
    {
        $this->expectException(MissingChargeInformationException::class);

        new LookupThreeDsTwoCommand(
            '12345',
            $this->faker->uuid,
            '',
            $this->createMock(Payment::class)
        );
    }
}
