<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use Rector\BetterReflection\Reflector\MethodReflector;
use Rector\NodeTypeResolver\TypesExtractor\ConstructorPropertyTypesExtractor;
use ReflectionFunction;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Inspired by https://github.com/nikic/PHP-Parser/blob/9373a8e9f551516bc8e42aedeacd1b4f635d27fc/lib/PhpParser/NameContext.php.
 */
final class TypeContext
{
    /**
     * @var mixed[]
     */
    private $types = [];

    /**
     * @var string[]
     */
    private $classProperties = [];

    /**
     * @var ClassLike|null
     */
    private $classLikeNode;

    /**
     * @var ConstructorPropertyTypesExtractor
     */
    private $constructorPropertyTypesExtractor;

    /**
     * @var MethodReflector
     */
    private $methodReflector;

    public function __construct(
        ConstructorPropertyTypesExtractor $constructorPropertyTypesExtractor,
        MethodReflector $methodReflector
    ) {
        $this->constructorPropertyTypesExtractor = $constructorPropertyTypesExtractor;
        $this->methodReflector = $methodReflector;
    }

    public function startFile(): void
    {
        $this->types = [];
        $this->classProperties = [];
        $this->classLikeNode = null;
    }

    public function addVariableWithType(string $variableName, string $variableType): void
    {
        $this->types[$variableName] = $variableType;
    }

    public function enterClass(ClassLike $classLikeNode): void
    {
        $this->classProperties = [];
        $this->classLikeNode = $classLikeNode;

        if ($classLikeNode instanceof Class_ && ! $classLikeNode->isAnonymous()) {
            $this->classProperties = $this->constructorPropertyTypesExtractor->extractFromClassNode($classLikeNode);
        }
    }

    public function enterFunction(FunctionLike $functionLikeNode): void
    {
        $this->types = [];

        $functionReflection = $this->getFunctionReflection($functionLikeNode);
        if ($functionReflection) {
            foreach ($functionReflection->getParameters() as $parameterReflection) {
                $this->types[$parameterReflection->getName()] = (string) $parameterReflection->getType();
            }
        }
    }

    public function getTypeForVariable(string $name): string
    {
        return $this->types[$name] ?? '';
    }

    public function getTypeForProperty(string $name): string
    {
        return $this->classProperties[$name] ?? '';
    }

    public function addAssign(string $newVariable, string $oldVariable): void
    {
        $type = $this->getTypeForVariable($oldVariable);
        $this->addVariableWithType($newVariable, $type);
    }

    /**
     * @return \Roave\BetterReflection\Reflection\ReflectionFunction|ReflectionMethod|null
     */
    private function getFunctionReflection(FunctionLike $functionLikeNode)
    {
        if ($this->classLikeNode) {
            if ($functionLikeNode instanceof Closure) {
                return null;
            }

            $className = $this->classLikeNode->namespacedName->toString();
            $methodName = (string) $functionLikeNode->name;

            return $this->methodReflector->reflectClassMethod($className, $methodName);
        }

        return new ReflectionFunction((string) $functionLikeNode->name);
    }
}
