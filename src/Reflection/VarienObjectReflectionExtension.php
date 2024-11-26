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
            echo "A\n";
            return false;
        }
        if (!$classReflection->is(\Varien_Object::class)) {
            return false;
        }

        if ($classReflection->isSubclassOf(\Varien_Object::class) && $this->enforceDocBlock) {
            $phpDocTags = $classReflection->getResolvedPhpDoc()->getMethodTags();
            return isset($phpDocTags[$methodName]);
        }

        return true;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new DummyMethodReflection($methodName);
    }
}
