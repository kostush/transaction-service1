<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\PublishDomainEvent;

/**
 * Class Model
 */
abstract class Entity
{
    /**
     * Returns the child entities for this model
     * Should be overriden if the model has child entities
     *
     * @return array
     */
    public function getChildEntities(): array
    {
        return [];
    }

    /**
     * Returns the entity name
     *
     * @return string
     */
    abstract public function getEntityName(): string;

    /**
     * Returns entity id
     *
     * @return string
     */
    abstract public function getEntityId(): string;
}
