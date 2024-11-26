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
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Identifier;
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

        $calledOnType = isset($methodCall->var)
            ? $scope->getType($methodCall->var)
            : $scope->resolveTypeByName($methodCall->class);

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

        $invalidExpr = \ltrim($this->exprPrinter->printExpr($methodCall), '\\');
        $message = \sprintf('Call to %s resulted in invalid type %s.', $invalidExpr, \implode('|', $invalidTypes));

        return [
            RuleErrorBuilder::message($message)->build()
        ];

        //return $this->buildMessage('Call to %s resulted in invalid type %s.', $invalidExpr, \implode('|', $invalidTypes));
    }

    protected function buildMessage(string $format, mixed ...$values): array
    {
        return [
            RuleErrorBuilder::message(\sprintf($format, ...$values))->build()
        ];
    }

}
