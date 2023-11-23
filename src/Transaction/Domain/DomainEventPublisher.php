<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain;

class DomainEventPublisher
{
    /** @var array|DomainEventSubscriber[] */
    private $subscribers;

    /** @var DomainEventPublisher */
    private static $instance = null;

    /**
     * @return array|DomainEventSubscriber[]
     */
    public function subscribers()
    {
        return $this->subscribers;
    }

    /**
     * @return DomainEventPublisher
     */
    public static function instance(): self
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * DomainEventPublisher constructor.
     */
    private function __construct()
    {
        $this->subscribers = [];
    }

    /**
     * @return void
     */
    public function __clone()
    {
        throw new \BadMethodCallException('Clone is not supported');
    }

    /**
     * Added to help with testing only due to the singleton
     * @return void
     */
    public static function tearDown()
    {
        static::$instance = null;
    }

    /**
     * @param DomainEventSubscriber $eventSubscriber Event Subscriber
     * @return void
     */
    public function subscribe(DomainEventSubscriber $eventSubscriber): void
    {
        $this->subscribers[] = $eventSubscriber;
    }

    /**
     * @param DomainEvent $event Event
     * @return void
     */
    public function publish(DomainEvent $event): void
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber->isSubscribedTo($event)) {
                $subscriber->handle($event);
            }
        }
    }
}
