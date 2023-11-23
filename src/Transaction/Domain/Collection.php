<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Collection
 */
abstract class Collection extends ArrayCollection
{
    /**
     * Validates the object
     *
     * @param mixed $object object
     *
     * @return bool
     */
    abstract protected function isValidObject($object);

    /**
     * Adds given value to collection
     *
     * @param mixed $offset Offset
     * @param mixed $value  Value
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->isValidObject($value)) {
            throw new \InvalidArgumentException('Invalid type for collection "' . get_class($this) . '"');
        }

        parent::offsetSet($offset, $value);
    }
}
