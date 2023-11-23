<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\Chargedback;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use Tests\UnitTestCase;
use ProBillerNG\Transaction\Domain\Model\Status;

class ChargedbackTest extends UnitTestCase
{
    /**
     * @test
     * @return Status
     */
    public function it_should_create_a_chargedback_status_class()
    {
        $status = Chargedback::create();

        $this->assertInstanceOf(Chargedback::class, $status);

        return $status;
    }

    /**
     * @test
     * @param Status $status The Status object
     * @depends it_should_create_a_chargedback_status_class
     * @return void
     */
    public function chargedback_status_class_should_return_chargedback_string($status)
    {
        $this->assertEquals('chargedback', (string) $status);
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_chargedback_to_aborted()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Chargedback::create();

        $status->abort();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_chargedback_to_declined()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Chargedback::create();

        $status->decline();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_chargedback_to_approved()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Chargedback::create();

        $status->approve();
    }

    /**
     * @test
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_not_allow_changing_from_chargedback_to_refunded()
    {
        $this->expectException(IllegalStateTransitionException::class);

        $status = Chargedback::create();

        $status->refund();
    }

    /**
     * @test
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function it_should_allow_changing_from_chargedback_to_chargedback()
    {
        /** @var Chargedback $status */
        $status = Chargedback::create();

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
        $createFromStringStatus = AbstractStatus::createFromString(Chargedback::NAME);
        $this->assertInstanceOf(Chargedback::class, $createFromStringStatus);
    }
}
