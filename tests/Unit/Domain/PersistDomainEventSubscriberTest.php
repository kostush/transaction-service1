<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use ProBillerNG\Transaction\Domain\DomainEvent;
use ProBillerNG\Transaction\Domain\EventStore;
use ProBillerNG\Transaction\Domain\PersistDomainEventSubscriber;
use Tests\UnitTestCase;

class PersistDomainEventSubscriberTest extends UnitTestCase
{
    /**
     * @var EventStore
     */
    protected $eventStore;

    /**
     * @var PersistDomainEventSubscriber
     */
    protected $eventPublisher;

    /**
     * Regular setup
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->eventStore     = $this->prophesize(EventStore::class);
        $this->eventPublisher = new PersistDomainEventSubscriber($this->eventStore->reveal());
    }

    /**
     * @test
     * @return void
     */
    public function handle_should_append_to_the_event_store()
    {
        $event = $this->prophesize(DomainEvent::class);
        $this->eventStore->append($event->reveal())->shouldBeCalled();
        $this->eventPublisher->handle($event->reveal());
    }

    /**
     * @test
     * @return void
     */
    public function should_subscribe_to_any_event()
    {
        $event = $this->prophesize(DomainEvent::class);
        $this->assertTrue($this->eventPublisher->isSubscribedTo($event->reveal()));
    }
}
