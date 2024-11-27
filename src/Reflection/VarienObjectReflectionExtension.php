<?php declare(strict_types=1);

/**
 * @category   Maho
 * @package    PHPStanPlugin
 * @copyright  Maho Contributors https://mahocommerce.com
 * @license    https://opensource.org/license/mit
 */

namespace Maho\PHPStanPlugin\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use Varien_Object;
use function in_array;
use function substr;

final class VarienObjectReflectionExtension implements MethodsClassReflectionExtension
{
    public function __construct(private bool $enforceDocBlock)
    {
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (!in_array(substr($methodName, 0, 3), ['get', 'set', 'uns', 'has'])) {
            return false;
        }
        if (!$classReflection->is(Varien_Object::class)) {
            return false;
        }

        $phpDocTags = $classReflection->getResolvedPhpDoc()->getMethodTags();

        if (isset($phpDocTags[$methodName])) {
            return false;
        }

        if ($classReflection->isSubclassOf(Varien_Object::class) && $this->enforceDocBlock) {
            return false;
        }

        return true;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new VarienObjectMagicMethodReflection($classReflection, $methodName);
    }
}
