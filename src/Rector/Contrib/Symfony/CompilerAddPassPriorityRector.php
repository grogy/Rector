<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Covers @see \Symfony\Component\DependencyInjection\Compiler\Compiler::addPass()
 *
 * Before:
 * - add($pass)
 * - add($pass, $type)
 * - add($pass, $type, 0)
 *
 * After:
 * ?
 */
final class CompilerAddPassPriorityRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if ($this->methodCallAnalyzer->isMethodCallTypeAndMethods(
            $node,
            'Symfony\Component\DependencyInjection\Compiler\Compiler',
            ['addPass']
        ) === false) {
            return false;
        }

        /** @var MethodCall $node */
        $args = $node->args;

        // has 3 arguments, all is done
        return count($args) !== 3;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $arguments = $methodCallNode->args;

        if (count($arguments) === 2) {
            // add 3rd one
            $arguments[] = $this->nodeFactory->createArg(0);

            $methodCallNode->args = $arguments;
        }

        return $methodCallNode;
    }
}
