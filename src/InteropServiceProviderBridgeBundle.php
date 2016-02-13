<?php

namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class InteropServiceProviderBridgeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ServiceProviderCompilationPass());
    }
}
