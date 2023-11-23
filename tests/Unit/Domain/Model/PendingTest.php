<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\Status;
use Tests\UnitTestCase;

class PendingTest extends UnitTestCase
{
    /**
     * @test
     * @return Status
     */
    public function it_should_create_a_pending_status_class()
    {
        $status = Pending::create();

        $this->assertInstanceOf(Pending::class, $status);

        return $status;
    }

    /**
     * @test
     * @param Status $status The Status object
     * @depends it_should_create_a_pending_status_class
     * @return void
     */
    public function pending_status_class_should_return_pending_string($status)
    {
        $this->assertEquals('pending', (string) $status);
    }

    /**
     * @test
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_pending_to_aborted()
    {
        /** @var Pending $status */
        $status = Pending::create();

        $newStatus = $status->abort();

        $this->assertInstanceOf(Aborted::class, $newStatus);
    }

    /**
     * @test
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_pending_to_declined()
    {
        /** @var Pending $status */
        $status = Pending::create();

        $newStatus = $status->decline();

        $this->assertInstanceOf(Declined::class, $newStatus);
    }

    /**
     * @test
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_pending_to_approved()
    {
        /** @var Pending $status */
        $status = Pending::create();

        $newStatus = $status->approve();

        $this->assertInstanceOf(Approved::class, $newStatus);
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_pending_to_refunded()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Pending::create();

        $status->refund();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_pending_to_chargedback()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Pending::create();

        $status->chargeback();
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_create_status_using_create_from_string()
    {
        $createFromStringStatus = AbstractStatus::createFromString(Pending::NAME);
        $this->assertInstanceOf(Pending::class, $createFromStringStatus);
    }
}
