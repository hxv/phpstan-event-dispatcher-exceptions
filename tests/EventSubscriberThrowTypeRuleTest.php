<?php

namespace hxv\PHPStanEventDispatcherExceptions\Tests;

use hxv\PHPStanEventDispatcherExceptions\EventClassResolver;
use hxv\PHPStanEventDispatcherExceptions\EventSubscriberThrowTypeRule;
use PHPStan\Rules\Exceptions\ExceptionTypeResolver;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @covers \hxv\PHPStanEventDispatcherExceptions\EventSubscriberThrowTypeRule
 * @covers \hxv\PHPStanEventDispatcherExceptions\EventClassResolver
 */
class EventSubscriberThrowTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new EventSubscriberThrowTypeRule(new EventClassResolver(), self::getContainer()->getByType(ExceptionTypeResolver::class));
    }

    public function testFoo(): void
    {
        $this->analyse([__DIR__ . '/data/HandlingEvents.php'], [
            [
                'Method hxv\PHPStanEventDispatcherExceptions\Tests\data\HandlingEvents::handleEventWithoutException() handling event hxv\PHPStanEventDispatcherExceptions\Tests\data\EventWithoutException throws RuntimeException, but event only allows void.',
                25,
            ],
            [
                'Method hxv\PHPStanEventDispatcherExceptions\Tests\data\HandlingEvents::handleEventWithRuntimeException2() handling event hxv\PHPStanEventDispatcherExceptions\Tests\data\EventWithRuntimeException throws LogicException|RuntimeException, but event only allows RuntimeException.',
                42,
            ],
        ]);
    }
}
