<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidStatusNameException;

abstract class AbstractStatus implements Status
{
    /**
     * Status constructor.
     */
    protected function __construct()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public static function create(): AbstractStatus
    {
        return new static();
    }

    /**
     * @param string $status Status
     * @return AbstractStatus
     * @throws \Exception
     */
    public static function createFromString(string $status): self
    {
        switch ($status) {
            case Pending::NAME:
                return Pending::create();
            case Approved::NAME:
                return Approved::create();
            case Refunded::NAME:
                return Refunded::create();
            case Chargedback::NAME:
                return Chargedback::create();
            case Aborted::NAME:
                return Aborted::create();
            case Declined::NAME:
                return Declined::create();
            default:
                throw new InvalidStatusNameException($status);
        }
    }

    /**
     * @return bool
     */
    public function pending(): bool
    {
        return $this instanceof Pending;
    }

    /**
     * @return bool
     */
    public function approved(): bool
    {
        return $this instanceof Approved;
    }

    /**
     * @return bool
     */
    public function refunded(): bool
    {
        return $this instanceof Refunded;
    }

    /**
     * @return bool
     */
    public function chargedback(): bool
    {
        return $this instanceof Chargedback;
    }

    /**
     * @return bool
     */
    public function aborted(): bool
    {
        return $this instanceof Aborted;
    }

    /**
     * @return bool
     */
    public function declined(): bool
    {
        return $this instanceof Declined;
    }

    /**
     * Approve a transaction
     *
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function approve()
    {
        throw new IllegalStateTransitionException();
    }

    /**
     * Decline a transaction
     *
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function decline()
    {
        throw new IllegalStateTransitionException();
    }

    /**
     * Abort a transaction
     *
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function abort()
    {
        throw new IllegalStateTransitionException();
    }

    /**
     * Refund a transaction
     *
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function refund()
    {
        throw new IllegalStateTransitionException();
    }

    /**
     * Chargeback a transaction
     *
     * @throws IllegalStateTransitionException
     * @throws DomainException
     * @throws \Exception
     * @return void
     */
    public function chargeback()
    {
        throw new IllegalStateTransitionException();
    }
}
