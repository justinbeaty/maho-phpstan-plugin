<?php declare(strict_types=1);

namespace Maho\PHPStanPlugin\Rules;

use Maho\PHPStanPlugin\Config\MageCoreConfig;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function class_exists;
use function count;
use function implode;
use function is_callable;
use function ltrim;
use function sprintf;

/**
 * @implements \PHPStan\Rules\Rule<Node\Expr\CallLike>
 */
final class MageInvalidTypeRule implements Rule
{
    public function __construct(private ExprPrinter $exprPrinter, private MageCoreConfig $mageCoreConfig)
    {
    }

    public function getNodeType(): string
    {
        return Node\Expr\CallLike::class;
    }

    public function processNode(Node $methodCall, Scope $scope): array
    {
        if (!$methodCall instanceof Node\Expr\MethodCall && !$methodCall instanceof Node\Expr\StaticCall) {
            return [];
        }
        if (!$methodCall->name instanceof Node\Identifier) {
            return [];
        }
        if (count($methodCall->getArgs()) === 0) {
            return [];
        }

        if ($methodCall instanceof Node\Expr\MethodCall) {
            $calledOnType = $scope->getType($methodCall->var);
        } elseif ($methodCall instanceof Node\Expr\StaticCall) {
            if ($methodCall->class instanceof Node\Name) {
                $calledOnType = $scope->resolveTypeByName($methodCall->class);
            } else {
                $calledOnType = $scope->getType($methodCall->class);
            }
        }

        $methodReflection = $scope->getMethodReflection($calledOnType, $methodCall->name->toString());

        if ($methodReflection === null) {
            return [];
        }

        $fn = $this->mageCoreConfig->getClassNameConverterFunction(
            $methodReflection->getDeclaringClass()->getName(),
            $methodReflection->getName()
        );

        if (!is_callable($fn)) {
            return [];
        }

        $aliases = $scope->getType($methodCall->getArgs()[0]->value)->getConstantStrings();

        $invalidTypes = [];

        foreach ($aliases as $alias) {

            $className = $fn($alias->getValue());

            if ($className === false) {
                $invalidTypes[] = 'bool(false)';
            } elseif (class_exists($className) === false) {
                $invalidTypes[] = $className;
            }
        }

        if (count($invalidTypes) === 0) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Call to %s resulted in invalid type %s.',
                ltrim($this->exprPrinter->printExpr($methodCall), '\\'),
                implode('|', $invalidTypes),
            ))->identifier('mage.invalidType')->build()
        ];
    }
}
