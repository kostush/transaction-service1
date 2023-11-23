<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class Pending extends AbstractStatus
{
    const NAME = 'pending';

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
    public function decline(): AbstractStatus
    {
        return Declined::create();
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public function abort(): AbstractStatus
    {
        return Aborted::create();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::NAME;
    }
}
