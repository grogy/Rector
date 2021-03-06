<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Rector\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Rector\NodeValueResolver\DependencyInjection\CompilerPass\NodeValueResolverCollectorCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    /**
     * @var string
     */
    private $config;

    public function __construct(?string $config = '')
    {
        $this->config = $config;
        parent::__construct('dev', true);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/services.yml');
        $loader->load(__DIR__ . '/../../packages/NodeTypeResolver/src/config/services.yml');

        if ($this->config) {
            $loader->load($this->config);
        }
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/_rector_cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/_rector_log';
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [];
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new CollectorCompilerPass);
        $containerBuilder->addCompilerPass(new NodeValueResolverCollectorCompilerPass);
    }
}
