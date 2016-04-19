<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use Interop\Container\ServiceProvider;
use Invoker\Reflection\CallableReflection;
use Puli\Discovery\Binding\ClassBinding;
use Puli\GeneratedPuliFactory;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use TheCodingMachine\ServiceProvider\Registry;

class ServiceProviderCompilationPass implements CompilerPassInterface
{
    private $registryId;

    /**
     * @var array
     */
    private $serviceProvidersLazyArray;

    /**
     * @var bool
     */
    private $usePuli;

    private $bundle;

    /**
     * @param int $registryId
     * @param array $serviceProvidersLazyArray
     * @param bool $usePuli
     */
    public function __construct($registryId, array $serviceProvidersLazyArray, $usePuli, InteropServiceProviderBridgeBundle $bundle)
    {
        $this->registryId = $registryId;
        $this->serviceProvidersLazyArray = $serviceProvidersLazyArray;
        $this->usePuli = $usePuli;
        $this->bundle = $bundle;
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

        $registry = $this->bundle->getRegistry($container);

        // Note: in the 'boot' method of a bundle, the container is available.
        // We use that to push the lazy array in the container.
        // The lazy array can be used by the registry that is also part of the container.
        // The registry can itself be used by a factory that creates services!

        $this->registerAcclimatedContainer($container);

        foreach ($registry as $serviceProviderKey => $serviceProvider) {
            $this->registerProvider($serviceProviderKey, $serviceProvider, $container);
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

    private function registerProvider($serviceProviderKey, ServiceProvider $serviceProvider, ContainerBuilder $container) {
        $serviceFactories = $serviceProvider->getServices();

        foreach ($serviceFactories as $serviceName => $callable) {
            $this->registerService($serviceName, $serviceProviderKey, $serviceProvider, $callable, $container);
        }
    }

    private function registerService($serviceName, $serviceProviderKey, ServiceProvider $serviceProvider, $callable, ContainerBuilder $container) {
        $factoryDefinition = $this->getServiceDefinitionFromCallable($serviceName, $serviceProviderKey, $callable);

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

            $callbackWrapperName = $serviceName.'.callbackwrapper';
            $callbackWrapperDefinition = new Definition('TheCodingMachine\\Interop\\ServiceProviderBridgeBundle\\CallableService', [
                new Reference('service_container'),
                $innerName
            ]);

            $factoryDefinition->addArgument(new Reference($callbackWrapperName));

            $container->setDefinition($serviceName, $factoryDefinition);
            $container->setDefinition($innerName, $innerDefinition);
            $container->setDefinition($callbackWrapperName, $callbackWrapperDefinition);

            $container->setAlias($oldServiceName, new Alias($serviceName));
        }

    }

    private function getDecoratedServiceName($serviceName, ContainerBuilder $container) {
        $counter = 1;
        while ($container->has($serviceName.'_decorated_'.$counter)) {
            $counter++;
        }
        return $serviceName.'_decorated_'.$counter;
    }

    private function getServiceDefinitionFromCallable($serviceName, $serviceProviderKey, callable $callable)
    {
        /*if ($callable instanceof DefinitionInterface) {
            // TODO: plug the definition-interop converter here!
        }*/
        $factoryDefinition = new Definition('Class'); // TODO: in PHP7, we can get the return type of the function!
        $containerDefinition = new Reference('interop_service_provider_acclimated_container');

        $callableReflection = CallableReflection::create($callable);

        if ($callableReflection instanceof \ReflectionMethod && $callableReflection->isStatic() && $callableReflection->isPublic()) {
            $factoryDefinition->setFactory([
                $callableReflection->getDeclaringClass()->getName(),
                $callableReflection->getName()
            ]);
            $factoryDefinition->addArgument(new Reference('interop_service_provider_acclimated_container'));
        } elseif ($callableReflection instanceof \ReflectionFunction) {
            $factoryDefinition->setFactory($callableReflection->getName());
            $factoryDefinition->addArgument(new Reference('interop_service_provider_acclimated_container'));
        } else {
            $factoryDefinition->setFactory([ new Reference('service_provider_registry_'.$this->registryId), 'createService' ]);
            $factoryDefinition->addArgument($serviceProviderKey);
            $factoryDefinition->addArgument($serviceName);
            $factoryDefinition->addArgument($containerDefinition);
        }

        return $factoryDefinition;
    }
}
