<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Chargedback;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Refunded;
use ProBillerNG\Transaction\Domain\Model\Status;
use Tests\UnitTestCase;

class ApprovedTest extends UnitTestCase
{
    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return Status
     */
    public function it_should_create_a_approved_status_class()
    {
        $status = Approved::create();

        $this->assertInstanceOf(Approved::class, $status);

        return $status;
    }

    /**
     * @test
     * @param Status $status The Status object
     * @depends it_should_create_a_approved_status_class
     * @return void
     */
    public function approved_status_class_should_return_approved_string($status)
    {
        $this->assertEquals('approved', (string) $status);
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_approved_to_aborted()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Approved::create();

        $status->abort();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_approved_to_declined()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Approved::create();

        $status->decline();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_approved_to_approved()
    {
        /** @var Approved $status */
        $status = Approved::create();

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
    public function it_should_allow_changing_from_approved_to_refunded()
    {
        /** @var Approved $status */
        $status = Approved::create();

        $newStatus = $status->refund();

        $this->assertInstanceOf(Refunded::class, $newStatus);
    }

    /**
     * @test
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_approved_to_chargedback()
    {
        /** @var Approved $status */
        $status = Approved::create();

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
        $createFromStringStatus = AbstractStatus::createFromString(Approved::NAME);
        $this->assertInstanceOf(Approved::class, $createFromStringStatus);
    }
}
