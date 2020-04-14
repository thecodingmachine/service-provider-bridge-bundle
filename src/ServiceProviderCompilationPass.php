<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use Interop\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TheCodingMachine\ServiceProvider\Registry;

class ServiceProviderCompilationPass implements CompilerPassInterface
{
    private $registryId;

    private $registryProvider;

    /**
     * @param int $registryId
     * @param RegistryProviderInterface $registryProvider
     */
    public function __construct($registryId, RegistryProviderInterface $registryProvider)
    {
        $this->registryId = $registryId;
        $this->registryProvider = $registryProvider;
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // Now, let's store the registry in the container (an empty version of it... it will be dynamically added at runtime):
        $this->registerRegistry($container);

        $registry = $this->registryProvider->getRegistry($container);

        // Note: in the 'boot' method of a bundle, the container is available.
        // We use that to push the lazy array in the container.
        // The lazy array can be used by the registry that is also part of the container.
        // The registry can itself be used by a factory that creates services!

        $this->registerAcclimatedContainer($container);

        foreach ($registry as $serviceProviderKey => $serviceProvider) {
            $this->registerFactories($serviceProviderKey, $serviceProvider, $container);
        }

        foreach ($registry as $serviceProviderKey => $serviceProvider) {
            $this->registerExtensions($serviceProviderKey, $serviceProvider, $container);
        }
    }


    private function registerRegistry(ContainerBuilder $container)
    {
        $definition = new Definition(Registry::class);
        $definition->setSynthetic(true);

        $container->setDefinition('service_provider_registry_'.$this->registryId, $definition);
    }

    private function registerAcclimatedContainer(ContainerBuilder $container) {
        $definition = new Definition('TheCodingMachine\\Interop\\ServiceProviderBridgeBundle\\SymfonyContainerAdapter');
        $definition->addArgument(new Reference("service_container"));

        $container->setDefinition('interop_service_provider_acclimated_container', $definition);
    }

    private function registerFactories($serviceProviderKey, ServiceProviderInterface $serviceProvider, ContainerBuilder $container) {
        $serviceFactories = $serviceProvider->getFactories();

        foreach ($serviceFactories as $serviceName => $callable) {
            $this->registerService($serviceName, $serviceProviderKey, $callable, $container);
        }
    }

    private function registerExtensions($serviceProviderKey, ServiceProviderInterface $serviceProvider, ContainerBuilder $container) {
        $serviceFactories = $serviceProvider->getExtensions();

        foreach ($serviceFactories as $serviceName => $callable) {
            $this->extendService($serviceName, $serviceProviderKey, $callable, $container);
        }
    }

    private function registerService($serviceName, $serviceProviderKey, $callable, ContainerBuilder $container) {
        $factoryDefinition = $this->getServiceDefinitionFromCallable($serviceName, $serviceProviderKey, $callable);

        $container->setDefinition($serviceName, $factoryDefinition);
    }

    private function extendService($serviceName, $serviceProviderKey, $callable, ContainerBuilder $container) {
        $factoryDefinition = $this->getServiceDefinitionFromCallable($serviceName, $serviceProviderKey, $callable, true);

        if (!$container->has($serviceName)) {
            $container->setDefinition($serviceName, $factoryDefinition);
        } else {
            // The new service will be created under the name 'xxx_decorated_y'
            // The old service will be moved to the name 'xxx_decorated_y.inner'
            // This old service will be accessible through a callback represented by 'xxx_decorated_y.callbackwrapper'
            // The $servicename becomes an alias pointing to 'xxx_decorated_y'

            $oldServiceName = $serviceName;
            $serviceName = $this->getDecoratedServiceName($serviceName, $container);

            $innerName = $serviceName.'.inner';
            $innerDefinition = $container->findDefinition($oldServiceName);
            $container->setDefinition($innerName, $innerDefinition);

            $factoryDefinition->addArgument(new Reference($innerName));

            $container->setDefinition($serviceName, $factoryDefinition);
            $container->setDefinition($innerName, $innerDefinition);

            $container->setAlias($oldServiceName, (new Alias($serviceName))->setPublic(true));
        }
    }

    private function getDecoratedServiceName($serviceName, ContainerBuilder $container) {
        $counter = 1;
        while ($container->has($serviceName.'_decorated_'.$counter)) {
            $counter++;
        }
        return $serviceName.'_decorated_'.$counter;
    }

    private function getServiceDefinitionFromCallable($serviceName, $serviceProviderKey, callable $callable, $isExtension = false)
    {
        /*if ($callable instanceof DefinitionInterface) {
            // TODO: plug the definition-interop converter here!
        }*/
        $factoryDefinition = new Definition('Class'); // TODO: in PHP7, we can get the return type of the function!
        $factoryDefinition->setPublic(true);
        $containerDefinition = new Reference('interop_service_provider_acclimated_container');

        if ((is_array($callable) && is_string($callable[0])) || is_string($callable)) {
            $factoryDefinition->setFactory($callable);
            $factoryDefinition->addArgument($containerDefinition);
        } else {
            $method = $isExtension ? 'extendService' : 'createService';
            $factoryDefinition->setFactory([ new Reference('service_provider_registry_'.$this->registryId), $method ]);
            $factoryDefinition->addArgument($serviceProviderKey);
            $factoryDefinition->addArgument($serviceName);
            $factoryDefinition->addArgument($containerDefinition);
        }

        return $factoryDefinition;
    }
}
