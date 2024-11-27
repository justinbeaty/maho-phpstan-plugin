<?php declare(strict_types=1);

/**
 * @category   Maho
 * @package    PHPStanPlugin
 * @copyright  Maho Contributors https://mahocommerce.com
 * @license    https://opensource.org/license/mit
 */

namespace Maho\PHPStanPlugin\Reflection;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
//use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;
use function substr;

final class VarienObjectMagicMethodReflection implements MethodReflection
{
    public function __construct(private ClassReflection $classReflection, private string $methodName)
    {
        switch (substr($methodName, 0, 3)) {
        case 'get':
            $this->methodReflection = $classReflection->getNativeMethod('getData');
            break;
        case 'set':
            $this->methodReflection = $classReflection->getNativeMethod('setData');
            break;
        case 'uns':
            $this->methodReflection = $classReflection->getNativeMethod('unsetData');
            break;
        case 'has':
            $this->methodReflection = $classReflection->getNativeMethod('hasData');
            break;
        default:
            throw new ShouldNotHappenException();
        }
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return $this->methodReflection->isStatic();
    }

    public function isPrivate(): bool
    {
        return $this->methodReflection->isPrivate();
    }

    public function isPublic(): bool
    {
        return $this->methodReflection->isPublic();
    }

    public function getDocComment(): ?string
    {
        return $this->methodReflection->getDocComment();
    }

    public function getName(): string
    {
        return $this->methodReflection->getName();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->methodReflection->getPrototype();
    }

    public function getVariants(): array
    {
        $variant = $this->methodReflection->getOnlyVariant();
        return [
            new FunctionVariant(
                $variant->getTemplateTypeMap(),
                $variant->getResolvedTemplateTypeMap(),
                array_slice($variant->getParameters(), 1),
                $variant->isVariadic(),
                $variant->getReturnType(),
            ),
        ];
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->methodReflection->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->methodReflection->getDeprecatedDescription();
    }

    public function isFinal(): TrinaryLogic
    {
        return $this->methodReflection->isFinal();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->methodReflection->isInternal();
    }

    public function getThrowType(): ?Type
    {
        return $this->methodReflection->getThrowType();
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return $this->methodReflection->hasSideEffects();
    }
}
