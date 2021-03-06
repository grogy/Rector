<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * Add attribute 'class' with current class name.
 * Add 'use_imports' with all related uses.
 */
final class ClassResolver extends NodeVisitorAbstract
{
    /**
     * @var ClassLike|null
     */
    private $classNode;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->classNode = null;
    }

    public function enterNode(Node $node): void
    {
        // detect only first ClassLike elemetn
        if ($this->classNode === null && $node instanceof ClassLike) {
            // skip possible anonymous classes
            if ($node instanceof Class_ && $node->isAnonymous()) {
                return;
            }

            $this->classNode = $node;
        }

        if ($this->classNode) {
            $node->setAttribute(Attribute::CLASS_NODE, $this->classNode);
            $node->setAttribute(Attribute::CLASS_NAME, $this->classNode->namespacedName->toString());
        }
    }
}
