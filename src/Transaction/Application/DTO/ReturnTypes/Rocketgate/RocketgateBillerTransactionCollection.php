<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate;

use ProBillerNG\Transaction\Domain\Collection;

class RocketgateBillerTransactionCollection extends Collection
{
    /**
     * {@inheritdoc}
     * @param mixed $object object
     * @return bool
     */
    protected function isValidObject($object)
    {
        return $object instanceof RocketgateBillerTransaction;
    }
}
