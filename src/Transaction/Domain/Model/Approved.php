<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class Approved extends AbstractStatus
{
    const NAME = 'approved';

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public function approve(): AbstractStatus
    {
        return Approved::create();
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public function refund(): AbstractStatus
    {
        return Refunded::create();
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public function chargeback(): AbstractStatus
    {
        return Chargedback::create();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::NAME;
    }
}
