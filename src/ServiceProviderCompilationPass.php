<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ServiceProviderCompilationPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('interop.service.providers')) {
            return;
        }

        $this->registerAcclimatedContainer($container);

        $serviceProviders = $container->getParameter('interop.service.providers');

        foreach ($serviceProviders as $serviceProvider) {
            $this->registerProvider($serviceProvider, $container);


        }
    }

    private function registerAcclimatedContainer(ContainerBuilder $container) {
        $definition = new Definition('TheCodingMachine\\Interop\\ServiceProviderBridgeBundle\\SymfonyContainerAdapter');
        $definition->addArgument(new Reference("service_container"));

        $container->addDefinitions([ 'interop_service_provider_acclimated_container' => $definition ]);
    }

    private function registerProvider($className, ContainerBuilder $container) {
        if (!is_string($className) || !class_exists($className)) {
            throw new ServiceProviderBridgeException('Error in parameter "interop.service.providers": providers should be fully qualified class names.');
        }

        $serviceFactories = call_user_func([$className, 'getServices']);

        foreach ($serviceFactories as $serviceName => $methodName) {
            $this->registerService($serviceName, $className, $methodName, $container);
        }
    }

    private function registerService($serviceName, $className, $methodName, ContainerBuilder $container) {

        $definition = new Definition('Class'); // TODO: in PHP7, we can get the return type of the function!
        $definition->setFactory([
            $className,
            $methodName
        ]);
        $arguments = [ new Reference('interop_service_provider_acclimated_container') ];

        if ($container->has($serviceName)) {
            $definition->setDecoratedService($serviceName);
            $serviceName = $this->getDecoratedServiceName($serviceName, $container);
            $arguments[] = new Reference($serviceName.'.inner');
        }

        $definition->setArguments($arguments);

        $container->addDefinitions([ $serviceName => $definition ]);
    }

    private function getDecoratedServiceName($serviceName, ContainerBuilder $container) {
        $counter = 1;
        while ($container->has($serviceName.'_decorated_'.$counter)) {
            $counter++;
        }
        return $serviceName.'_decorated_'.$counter;
    }
}
