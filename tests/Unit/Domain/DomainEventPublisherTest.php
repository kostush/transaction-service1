<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use ProBillerNG\Transaction\Domain\DomainEvent;
use ProBillerNG\Transaction\Domain\DomainEventPublisher;
use ProBillerNG\Transaction\Domain\DomainEventSubscriber;
use Tests\ClearSingletons;
use Tests\UnitTestCase;

class DomainEventPublisherTest extends UnitTestCase
{
    use ClearSingletons;

    /**
     * @var DomainEventPublisher
     */
    protected $domainEventPublisher;

    /**
     * Regular setup
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Since the DomainEventPublisher is a singleton we have to clear it to avoid contamination between tests
        $this->clearSingleton();
        $this->domainEventPublisher = DomainEventPublisher::instance();
    }

    /**
     * @test
     * @return void
     */
    public function newly_created_domain_publisher_should_have_no_subscribers()
    {
        $this->assertCount(0, $this->domainEventPublisher->subscribers());
    }

    /**
     * @test
     * @return void
     */
    public function clone_domain_publisher_should_throw_exception()
    {
        $this->expectException(\BadMethodCallException::class);
        $clonedDomainEventPublisher = clone $this->domainEventPublisher;
    }

    /**
     * @test
     * @return void
     */
    public function subscribe_should_add_to_the_list_of_subscribers()
    {
        $subscriber = $this->prophesize(DomainEventSubscriber::class);
        $this->domainEventPublisher->subscribe($subscriber->reveal());
        $this->assertCount(1, $this->domainEventPublisher->subscribers());
    }

    /**
     * @test
     * @return void
     */
    public function publish_should_call_handle_on_all_that_subscribed_to_the_event()
    {
        /** @var DomainEventSubscriber $firstSubscriber */
        $firstSubscriber = $this->prophesize(DomainEventSubscriber::class);
        /** @var DomainEventSubscriber $secondSubscriber */
        $secondSubscriber = $this->prophesize(DomainEventSubscriber::class);
        /** @var DomainEvent $event */
        $event = $this->prophesize(DomainEvent::class);

        $this->domainEventPublisher->subscribe($firstSubscriber->reveal());
        $this->domainEventPublisher->subscribe($secondSubscriber->reveal());

        // Adding expectations
        // The first subscriber will want the event, the second will not
        $firstSubscriber->isSubscribedTo($event->reveal())->willReturn(true);
        $secondSubscriber->isSubscribedTo($event->reveal())->willReturn(false);

        // So I should expect the first subscriber to be invoked to handle but not the second
        $firstSubscriber->handle($event->reveal())->shouldBeCalled();
        $secondSubscriber->handle($event->reveal())->shouldNotBeCalled();

        // Moment of truth
        $this->domainEventPublisher->publish($event->reveal());
    }
}
