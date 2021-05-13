<?php

namespace hxv\PHPStanEventDispatcherExceptions;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Exceptions\ExceptionTypeResolver;
use PHPStan\Rules\Rule;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @implements Rule<InClassMethodNode>
 */
class EventSubscriberThrowTypeRule implements Rule
{
    /** @var EventClassResolver */
    private $eventClassResolver;

    /** @var ExceptionTypeResolver */
    private $exceptionTypeResolver;

    public function __construct(EventClassResolver $eventClassResolver, ExceptionTypeResolver $exceptionTypeResolver)
    {
        $this->eventClassResolver = $eventClassResolver;
        $this->exceptionTypeResolver = $exceptionTypeResolver;
    }

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ((null === $class = $scope->getClassReflection()) || !$class->isSubclassOf(EventSubscriberInterface::class)) {
            return [];
        }

        /** @var class-string<EventSubscriberInterface> $subscriberClass */
        $subscriberClass = $class->getName();

        if ((null === $method = $scope->getFunction()) || !$method instanceof MethodReflection) {
            return [];
        }

        $actualThrow = $method->getThrowType();
        if ($actualThrow === null || $actualThrow instanceof VoidType) {
            return [];
        }

        if (null === $eventClass = $this->eventClassResolver->resolveEventClass($subscriberClass, $method)) {
            return [];
        }

        $eventClassType = $scope->resolveTypeByName(new Name($eventClass));

        if (null === $eventClassReflection = $eventClassType->getClassReflection()) {
            throw new LogicException(sprintf('Class %s does not have a reflection information.', $eventClassType->getClassName()));
        }

        if ((null !== $phpDoc = $eventClassReflection->getResolvedPhpDoc()) && (null !== $throwsTag = $phpDoc->getThrowsTag())) {
            $allowedThrow = $throwsTag->getType();
        } else {
            $allowedThrow = new VoidType();
        }

        $hasCheckedType = false;
        foreach ($actualThrow->getReferencedClasses() as $throwClass) {
            if ($this->exceptionTypeResolver->isCheckedException($throwClass)) {
                $hasCheckedType = true;
            }
        }

        if (!$hasCheckedType) {
            return [];
        }

        if ($allowedThrow->accepts($actualThrow, true)->yes()) {
            return [];
        }

        return [sprintf(
            'Method %s::%s() handling event %s throws %s, but event only allows %s.',
            $method->getDeclaringClass()->getDisplayName(),
            $method->getName(),
            $eventClassType->getClassName(),
            $actualThrow->describe(VerbosityLevel::typeOnly()),
            $allowedThrow->describe(VerbosityLevel::typeOnly())
        )];
    }
}
