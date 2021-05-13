[Since version 0.12.87](https://phpstan.org/blog/bring-your-exceptions-under-control) [PHPStan](https://phpstan.org/) analyses and checks
thrown exceptions - hooray! [`pepakriz/phpstan-exception-rules`](https://github.com/pepakriz/phpstan-exception-rules) does the same thing
(and more), but it's nice to have this out of the box.  
However, there are some cases that are "too dynamic" for static analysis to work automatically and thus require additional extensions.
This one aims to provide support for [Symfony's Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html).

# Problem
Every time you dispatch an event, any of the handlers can throw an exception - since PHPStan doesn't know anything about them you can:
- ignore them
- remember which event throws which exceptions
- analyse handlers
- do not throw any exceptions in handlers
- catch everything

If you don't choose first or last option, you have to be very careful when modifying handlers - new exceptions can cause problems.
If you do - well, you probably do not need this extension.

# Solution
Solution to above problem is to annotate exceptions with `@throws` tag on class level for every event:
```php
/**
 * @throws RuntimeException
 */
class SomeEvent
{
}
```

Having exceptions assigned to events analysis can be more complete and warn about new problems.

From now on PHPStan knows that dispatching that event can throw `RuntimeException`:
```php
try {
    $eventDispatcher->dispatch(new SomeEvent());
} catch (RuntimeException $exception) { // no more error!
    // handle exception
}
// new exceptions needs to be handled or annotated at function level - you will not miss them
```

There is also a rule to monitor exceptions thrown in subscribers:
```php
class SomeEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [SomeEvent::class => 'handleEvent'];
    }

    /**
     * @throws RuntimeException <-- this is fine
     * @throws LogicException <-- this is not (unless LogicException is unchecked)
     */
    public function handleEvent(): void
    {
    }
}
```

# Installation
Require extension in composer:
```bash
composer require --dev hxv/phpstan-event-dispatcher-exceptions
```

If you have [PHPStan Extension Installer](https://github.com/phpstan/extension-installer) - that's all!

If not - you have to manually add extension to your `phpstan.neon`:
```neon
includes:
	- vendor/hxv/phpstan-event-dispatcher-exceptions/extension.neon
```
