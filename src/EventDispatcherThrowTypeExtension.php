<?php

namespace hxv\PHPStanEventDispatcherExceptions;

use LogicException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodThrowTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventDispatcherThrowTypeExtension implements DynamicMethodThrowTypeExtension
{
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'dispatch'
            && (
                $methodReflection->getDeclaringClass()->isSubclassOf(EventDispatcherInterface::class)
                || $methodReflection->getDeclaringClass()->getName() === EventDispatcherInterface::class
            );
    }

    public function getThrowTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $type = $scope->getType($methodCall->args[0]->value);

        if (!$type instanceof ObjectType) {
            return null;
        }

        if (null === $classReflection = $type->getClassReflection()) {
            throw new LogicException(sprintf('Class %s does not have a reflection information.', $type->getClassName()));
        }

        if (null === $phpDoc = $classReflection->getResolvedPhpDoc()) {
            return null;
        }

        if (null === $throwsTag = $phpDoc->getThrowsTag()) {
            return null;
        }

        return $throwsTag->getType();
    }
}
