<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Rector\Contract\Rector\RectorInterface;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassMethodDeprecation;
use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\Deprecation\RemovedFunctionalityDeprecation;
use Rector\Exception\NotImplementedException;

/**
 * Creates rectors with propper setup based on found deprecations.
 */
final class RectorFactory
{
    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    /**
     * @var ConfigurableChangeMethodNameRector
     */
    private $configurableChangeMethodNameRector;

    public function __construct(
        DeprecationCollector $deprecationCollector,
        ConfigurableChangeMethodNameRector $configurableChangeMethodNameRector
    ) {
        $this->deprecationCollector = $deprecationCollector;
        $this->configurableChangeMethodNameRector = $configurableChangeMethodNameRector;
    }

    /**
     * @return RectorInterface[]
     */
    public function createRectors(): array
    {
        $rectors = [];

        foreach ($this->deprecationCollector->getDeprecations() as $deprecation) {
            if ($deprecation instanceof RemovedFunctionalityDeprecation) {
                continue;
            }

            $rectors[] = $this->createRectorFromDeprecation($deprecation);
        }

        return $rectors;
    }

    public function createRectorFromDeprecation(DeprecationInterface $deprecation): RectorInterface
    {
        if ($deprecation instanceof ClassMethodDeprecation) {
            $configurableChangeMethodNameRector = clone $this->configurableChangeMethodNameRector;
            $configurableChangeMethodNameRector->setPerClassOldToNewMethods([
                $deprecation->getOldClass() => [
                    $deprecation->getOldMethod() => $deprecation->getNewMethod(),
                ],
            ]);

            return $configurableChangeMethodNameRector;
        }

        throw new NotImplementedException(sprintf(
            '%s() was unable to create a Rector based on "%s" Deprecation. Create a new method there.',
            __METHOD__,
            get_class($deprecation)
        ));
    }
}
