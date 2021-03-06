<?php declare(strict_types=1);

namespace Rector\Contract\Rector;

use PhpParser\Node;

interface RectorInterface
{
    public function isCandidate(Node $node): bool;

    public function refactor(Node $node): ?Node;
}
