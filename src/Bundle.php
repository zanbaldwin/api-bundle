<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle;

use Intergalactic\ApiBundle\DependencyInjection\Extension as BundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle as KernelBundle;

class Bundle extends KernelBundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new BundleExtension;
    }
}
