<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\Chargedback;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Refunded;
use ProBillerNG\Transaction\Domain\Model\Status;
use Tests\UnitTestCase;

class RefundedTest extends UnitTestCase
{
    /**
     * @test
     * @return Status
     */
    public function it_should_create_a_refunded_status_class()
    {
        $status = Refunded::create();

        $this->assertInstanceOf(Refunded::class, $status);

        return $status;
    }

    /**
     * @test
     * @param Status $status The Status object
     * @depends it_should_create_a_refunded_status_class
     * @return void
     */
    public function refunded_status_class_should_return_refunded_string($status)
    {
        $this->assertEquals('refunded', (string) $status);
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_refunded_to_aborted()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Refunded::create();

        $status->abort();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_refunded_to_declined()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Refunded::create();

        $status->decline();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_refunded_to_approved()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Refunded::create();

        $status->approve();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_refunded_to_refunded()
    {
        /** @var Refunded $status */
        $status = Refunded::create();

        $newStatus = $status->refund();

        $this->assertInstanceOf(Refunded::class, $newStatus);
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_refunded_to_chargedback()
    {
        /** @var Refunded $status */
        $status = Refunded::create();

        $newStatus = $status->chargeback();

        $this->assertInstanceOf(Chargedback::class, $newStatus);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_create_status_using_create_from_string()
    {
        $createFromStringStatus = AbstractStatus::createFromString(Refunded::NAME);
        $this->assertInstanceOf(Refunded::class, $createFromStringStatus);
    }
}
