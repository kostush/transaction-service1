<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class Aborted extends AbstractStatus
{
    const NAME = 'aborted';

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
