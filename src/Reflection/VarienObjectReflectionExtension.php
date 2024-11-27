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

namespace Maho\PHPStanPlugin\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Dummy\DummyMethodReflection;

use PHPStan\Reflection\MethodsClassReflectionExtension;

final class VarienObjectReflectionExtension implements MethodsClassReflectionExtension
{
    public function __construct(private bool $enforceDocBlock)
    {
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (!\in_array(\substr($methodName, 0, 3), ['get', 'set', 'uns', 'has'])) {
            return false;
        }
        if (!$classReflection->is(\Varien_Object::class)) {
            return false;
        }

        $phpDocTags = $classReflection->getResolvedPhpDoc()->getMethodTags();

        if (isset($phpDocTags[$methodName])) {
            return false;
        }

        if ($classReflection->isSubclassOf(\Varien_Object::class) && $this->enforceDocBlock) {
            return false;
        }

        return true;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new DummyMethodReflection($methodName);
    }
}
