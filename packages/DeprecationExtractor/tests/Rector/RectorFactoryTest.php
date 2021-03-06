<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\Rector;

use PHPUnit\Framework\Assert;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\DeprecationExtractor\Rector\ConfigurableChangeMethodNameRector;
use Rector\DeprecationExtractor\Rector\RectorFactory;
use Rector\Tests\AbstractContainerAwareTestCase;

final class RectorFactoryTest extends AbstractContainerAwareTestCase
{
    /**
     * @var RectorFactory
     */
    private $rectorFactory;

    protected function setUp(): void
    {
        $this->rectorFactory = $this->container->get(RectorFactory::class);

        $DeprecationExtractor = $this->container->get(DeprecationExtractor::class);
        $DeprecationExtractor->scanDirectories([__DIR__ . '/../DeprecationExtractorSource']);
    }

    public function test(): void
    {
        $rectors = $this->rectorFactory->createRectors();
        $this->assertCount(2, $rectors);

        /** @var ConfigurableChangeMethodNameRector $secondRector */
        $secondRector = $rectors[0];
        $this->assertInstanceOf(ConfigurableChangeMethodNameRector::class, $secondRector);

        $this->assertSame([
            'Nette\DI\ServiceDefinition' => [
                'setInject' => 'addTag',
            ],
        ], Assert::getObjectAttribute($secondRector, 'perClassOldToNewMethod'));

        /** @var ConfigurableChangeMethodNameRector $firstRector */
        $firstRector = $rectors[1];
        $this->assertInstanceOf(ConfigurableChangeMethodNameRector::class, $firstRector);

        $this->assertSame([
            'Nette\DI\ServiceDefinition' => [
                'setClass' => 'setFactory',
            ],
        ], Assert::getObjectAttribute($firstRector, 'perClassOldToNewMethod'));
    }
}
