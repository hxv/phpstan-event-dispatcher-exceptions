<?php

namespace hxv\PHPStanEventDispatcherExceptions;

use PHPStan\Reflection\MethodReflection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventClassResolver
{
    /**
     * @param class-string<EventSubscriberInterface> $subscriberClass
     *
     * @return class-string|null
     */
    public function resolveEventClass(string $subscriberClass, MethodReflection $methodReflection): ?string
    {
        foreach ($this->getSubscribedEvents($subscriberClass) as $eventClass => $method) {
            if ($methodReflection->getName() === $method) {
                return $eventClass;
            }
        }

        return null;
    }

    /**
     * @param class-string<EventSubscriberInterface> $subscriberClass
     *
     * @return iterable<class-string, string>
     */
    private function getSubscribedEvents(string $subscriberClass): iterable
    {
        /** @var array<class-string, string|array{0: string, 1?: int}|array<array{0: string, 1?: int}>>> $subscribedEvents */
        $subscribedEvents = call_user_func([$subscriberClass, 'getSubscribedEvents']);

        foreach ($subscribedEvents as $eventClass => $subscriber) {
            if (is_string($subscriber)) {
                yield $eventClass => $subscriber;
            }

            if (is_array($subscriber)) {
                foreach ($subscriber as $foo) {
                    if (is_string($foo)) {
                        yield $eventClass => $foo;
                    } elseif (is_array($foo)) {
                        yield $eventClass => $foo[0];
                    }
                }
            }
        }
    }
}
