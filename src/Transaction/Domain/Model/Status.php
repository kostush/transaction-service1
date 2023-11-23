<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

interface Status
{
    /**
     * @return bool
     */
    public function pending(): bool;

    /**
     * @return bool
     */
    public function approved(): bool;

    /**
     * @return bool
     */
    public function refunded(): bool;

    /**
     * @return bool
     */
    public function chargedback(): bool;

    /**
     * @return bool
     */
    public function aborted(): bool;

    /**
     * @return bool
     */
    public function declined(): bool;
}
