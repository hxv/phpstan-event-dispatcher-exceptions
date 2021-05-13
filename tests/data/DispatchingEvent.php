<?php

namespace hxv\PHPStanEventDispatcherExceptions\Tests\data;

use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class DispatchingEvent
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(): void
    {
        try {
            $this->eventDispatcher->dispatch(new EventWithRuntimeException());
        } catch (RuntimeException $exception) {
            // valid
        }

        try {
            $this->eventDispatcher->dispatch(new EventWithRuntimeException());
        } catch (LogicException $exception) {
            // invalid
        }

        try {
            $this->eventDispatcher->dispatch(new EventWithoutException());
        } catch (RuntimeException $exception) {
            // invalid
        }
    }
}
