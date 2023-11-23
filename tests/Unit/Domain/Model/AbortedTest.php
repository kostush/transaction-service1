<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Status;
use Tests\UnitTestCase;

class AbortedTest extends UnitTestCase
{
    /**
     * @test
     * @return Status
     */
    public function it_should_create_a_aborted_status_class()
    {
        $status = Aborted::create();

        $this->assertInstanceOf(Aborted::class, $status);

        return $status;
    }

    /**
     * @test
     * @param Status $status The Status object
     * @depends it_should_create_a_aborted_status_class
     * @return void
     */
    public function aborted_status_class_should_return_aborted_string($status)
    {
        $this->assertEquals('aborted', (string) $status);
    }


    /**
     * @test
     * @return void
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     */
    public function it_should_allow_changing_from_aborted_to_aborted()
    {
        /** @var Aborted $status */
        $status = Aborted::create();

        $newStatus = $status->abort();

        $this->assertInstanceOf(Aborted::class, $newStatus);
    }

    /**
     * @test
     * @return void
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     */
    public function it_should_not_allow_changing_from_aborted_to_declined()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Aborted::create();

        $status->decline();
    }

    /**
     * @test
     * @return void
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     */
    public function it_should_not_allow_changing_from_aborted_to_approved()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Aborted::create();

        $status->approve();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_aborted_to_refunded()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Aborted::create();

        $status->refund();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_aborted_to_chargedback()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Aborted::create();

        $status->chargeback();
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_create_status_using_create_from_string()
    {
        $createFromStringStatus = AbstractStatus::createFromString(Aborted::NAME);
        $this->assertInstanceOf(Aborted::class, $createFromStringStatus);
    }
}
