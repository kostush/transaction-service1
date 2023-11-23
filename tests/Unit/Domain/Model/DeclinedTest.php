<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Status;
use Tests\UnitTestCase;

class DeclinedTest extends UnitTestCase
{
    /**
     * @test
     * @return Status
     */
    public function it_should_create_a_declined_status_class()
    {
        $status = Declined::create();

        $this->assertInstanceOf(Declined::class, $status);

        return $status;
    }

    /**
     * @test
     * @param Status $status The Status object
     * @depends it_should_create_a_declined_status_class
     * @return void
     */
    public function declined_status_class_should_return_declined_string($status)
    {
        $this->assertEquals('declined', (string) $status);
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_declined_to_aborted()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Declined::create();

        $status->abort();
    }

    /**
     * @test
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_declined_to_declined()
    {
        /** @var Declined $status */
        $status = Declined::create();

        $newStatus = $status->decline();

        $this->assertInstanceOf(Declined::class, $newStatus);
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_declined_to_approved()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Declined::create();

        $status->approve();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_declined_to_refunded()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Declined::create();

        $status->refund();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_declined_to_chargedback()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Declined::create();

        $status->chargeback();
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_create_status_using_create_from_string()
    {
        $createFromStringStatus = AbstractStatus::createFromString(Declined::NAME);
        $this->assertInstanceOf(Declined::class, $createFromStringStatus);
    }
}
