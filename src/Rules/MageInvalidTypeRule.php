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

namespace Maho\PHPStanPlugin\Rules;

use Maho\PHPStanPlugin\Config\MageCoreConfig;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use PHPStan\Node\Printer\ExprPrinter;

final class MageInvalidTypeRule implements Rule
{
    public function __construct(private ExprPrinter $exprPrinter, private MageCoreConfig $mageCoreConfig)
    {
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    public function processNode(Node $methodCall, Scope $scope): array
    {
        if (!$methodCall instanceof MethodCall && !$methodCall instanceof StaticCall) {
            return [];
        }
        if (!$methodCall->name instanceof Identifier) {
            return [];
        }

        if ($methodCall instanceof MethodCall) {
            $calledOnType = $scope->getType($methodCall->var);
        } elseif ($methodCall instanceof StaticCall) {
            if ($methodCall->class instanceof Node\Name) {
                $calledOnType = $scope->resolveTypeByName($methodCall->class);
            } else {
                $calledOnType = $scope->getType($methodCall->class);
            }
        } else {
            return [];
        }

        $methodReflection = $scope->getMethodReflection($calledOnType, $methodCall->name->toString());

        if ($methodReflection === null) {
            return [];
        }

        $fn = $this->mageCoreConfig->getConfigMethodClosure(
            $methodReflection->getDeclaringClass()->getName(),
            $methodReflection->getName()
        );

        if (!\is_callable($fn)) {
            return [];
        }

        // arguments.count
        if (\count($methodCall->getArgs()) === 0) {
            return [];
        }

        $aliases = $scope->getType($methodCall->getArgs()[0]->value)->getConstantStrings();

        $invalidTypes = [];

        foreach ($aliases as $alias) {

            $className = $fn($alias->getValue());

            if ($className === false) {
                $invalidTypes[] = 'bool(false)';
            } elseif (\class_exists($className) === false) {
                $invalidTypes[] = $className;
            }
        }

        if (\count($invalidTypes) === 0) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'Call to %s resulted in invalid type %s.',
                \ltrim($this->exprPrinter->printExpr($methodCall), '\\'),
                \implode('|', $invalidTypes),
            ))->identifier('mage.invalidType')->build()
        ];
    }
}
