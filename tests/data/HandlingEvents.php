<?php

namespace hxv\PHPStanEventDispatcherExceptions\Tests\data;

use BadMethodCallException;
use LogicException;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HandlingEvents implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            EventWithoutException::class => 'handleEventWithoutException',
            EventWithRuntimeException::class => [['handleEventWithRuntimeException'], ['handleEventWithRuntimeException2']],
            EventWithMultipleExceptions::class => ['handleEventWithMultipleExceptions'],
            EventWithUnionException::class => ['handleEventWithUnionException'],
        ];
    }

    /**
     * @throws RuntimeException
     */
    public function handleEventWithoutException(): void
    {
        // invalid
    }

    /**
     * @throws RuntimeException
     */
    public function handleEventWithRuntimeException(): void
    {
        // valid
    }

    /**
     * @throws RuntimeException
     * @throws LogicException
     */
    public function handleEventWithRuntimeException2(): void
    {
        // invalid
    }

    /**
     * @throws LogicException
     * @throws RuntimeException
     */
    public function handleEventWithMultipleExceptions(): void
    {
        // valid
    }

    /**
     * @throws BadMethodCallException
     */
    public function handleEventWithUnionException(): void
    {
        // valid
    }
}
