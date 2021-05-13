<?php

namespace hxv\PHPStanEventDispatcherExceptions\Tests;

use PHPStan\Rules\Exceptions\CatchWithUnthrownExceptionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @covers \hxv\PHPStanEventDispatcherExceptions\EventDispatcherThrowTypeExtension
 */
class EventDispatcherThrowTypeExtensionTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CatchWithUnthrownExceptionRule();
    }

    public function testPsrDispatcherExceptions(): void
    {
        $this->analyse([__DIR__ . '/data/DispatchingEvent.php'], [
            [
                'Dead catch - LogicException is never thrown in the try block.',
                29,
            ],
            [
                'Dead catch - RuntimeException is never thrown in the try block.',
                35,
            ],
        ]);
    }

    public function testSymfonyDispatcherExceptions(): void
    {
        $this->analyse([__DIR__ . '/data/DispatchingSymfonyEvent.php'], [
            [
                'Dead catch - LogicException is never thrown in the try block.',
                29,
            ],
            [
                'Dead catch - RuntimeException is never thrown in the try block.',
                35,
            ],
        ]);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../extension.neon'];
    }
}
