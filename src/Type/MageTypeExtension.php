<?php
/**
 * Maho
 *
 * @category   Maho
 * @package    PHPStanPlugin
 * @copyright  Copyright © Maho (https://mahocommerce.com)
 * @license    https://opensource.org/license/mit The MIT License
 */

declare(strict_types=1);

namespace Maho\PHPStanPlugin\Type;

use Maho\PHPStanPlugin\Config\MageCoreConfig;

use PhpParser\Node\Expr\CallLike;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Analyser\Scope;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ObjectType;

use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;

final class MageTypeExtension implements DynamicMethodReturnTypeExtension, DynamicStaticMethodReturnTypeExtension
{
    public function __construct(private string $className, private MageCoreConfig $mageCoreConfig)
    {
    }

    public function getClass(): string
    {
        return $this->className;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        $fn = $this->mageCoreConfig->getConfigMethodClosure(
            $methodReflection->getDeclaringClass()->getName(),
            $methodReflection->getName()
        );

        return \is_callable($fn);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, CallLike $methodCall, Scope $scope): ?Type
    {
        if (\count($methodCall->getArgs()) === 0) {
            return null; // do i return error type?
        }

        $fn = $this->mageCoreConfig->getConfigMethodClosure(
            $methodReflection->getDeclaringClass()->getName(),
            $methodReflection->getName()
        );

        $aliases = $scope->getType($methodCall->getArgs()[0]->value)->getConstantStrings();

        $returnTypes = [];

        foreach ($aliases as $alias) {

            $className = $fn($alias->getValue());

            if ($className === false || \class_exists($className) === false) {
                $returnTypes[] = new ConstantBooleanType(false);
            } else {
                $returnTypes[] = new ObjectType($className);
            }
        }

        if (\count($returnTypes) === 0) {
            $returnTypes[] = $methodReflection->getOnlyVariant()->getReturnType();
        }

        return TypeCombinator::union(...$returnTypes);
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $this->isMethodSupported($methodReflection);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, CallLike $methodCall, Scope $scope): ?Type
    {
        return $this->getTypeFromMethodCall($methodReflection, $methodCall, $scope);
    }
}
