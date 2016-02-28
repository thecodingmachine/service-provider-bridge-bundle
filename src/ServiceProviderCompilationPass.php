<?php


namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;


use Puli\Discovery\Binding\ClassBinding;
use Puli\GeneratedPuliFactory;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
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

        if ($container->hasParameter('interop.service.enable_puli')) {
            $enablePuli = $container->getParameter('interop.service.enable_puli');
        } else {
            $enablePuli = true;
        }

        $serviceProviders = [];

        if ($enablePuli) {
            try {
                $puliFactory = $container->get('puli.factory');
                /* @var $puliFactory GeneratedPuliFactory */
            } catch (InvalidArgumentException $exception) {
                throw new InvalidArgumentException('Could not find puli.factory in container. Make sure you add the Puli bundle to your AppKernel.php file. Alternatively, you can disable Puli detection using the interop.service.enable_puli = false parameter.', 0, $exception);
            }

            $discovery = $puliFactory->createDiscovery($puliFactory->createRepository());

            $bindings = $discovery->findBindings('container-interop/service-provider');


            foreach ($bindings as $binding) {
                if ($binding instanceof ClassBinding) {
                    $serviceProviders[] = $binding->getClassName();
                }
            }
        }

        if ($container->hasParameter('interop.service.providers')) {
            $serviceProviders = array_merge($serviceProviders, array_values($container->getParameter('interop.service.providers')));
        }

        $this->registerAcclimatedContainer($container);

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
            throw new ServiceProviderBridgeException('Error in parameter "interop.service.providers" or in Puli binding: providers should be fully qualified class names.');
        }

        $serviceFactories = call_user_func([$className, 'getServices']);

        foreach ($serviceFactories as $serviceName => $methodName) {
            $this->registerService($serviceName, $className, $methodName, $container);
        }
    }

    private function registerService($serviceName, $className, $methodName, ContainerBuilder $container) {
        $factoryDefinition = new Definition('Class'); // TODO: in PHP7, we can get the return type of the function!
        $factoryDefinition->setFactory([
            $className,
            $methodName
        ]);
        $arguments = [new Reference('interop_service_provider_acclimated_container')];

        if (!$container->has($serviceName)) {
            $factoryDefinition->setArguments($arguments);
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

            $arguments[] = new Reference($callbackWrapperName);
            $factoryDefinition->setArguments($arguments);

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
}
