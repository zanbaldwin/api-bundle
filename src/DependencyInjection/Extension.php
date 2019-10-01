<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as KernelExtension;

/**
 * @author Zan Baldwin <hello@zanbaldwin.com>
 */
class Extension extends KernelExtension
{
    public function getAlias(): string
    {
        return 'intergalactic-api';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        # $loader->load('services.yaml');
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);
    }
}
