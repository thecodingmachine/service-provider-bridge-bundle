<?php

namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;

use Puli\Discovery\Api\Discovery;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TheCodingMachine\ServiceProvider\Registry;

class InteropServiceProviderBridgeBundle extends Bundle
{
    private $serviceProviders;
    private $usePuli;
    private $id;

    private static $count = 0;

    /**
     * @param array $serviceProviders An array of service providers, in the format specified in thecodingmachine/service-provider-registry: https://github.com/thecodingmachine/service-provider-registry#how-does-it-work
     * @param bool $usePuli
     */
    public function __construct(array $serviceProviders = [], $usePuli = true)
    {
        $this->serviceProviders = $serviceProviders;
        $this->usePuli = $usePuli;
        $this->id = self::$count;
        self::$count++;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ServiceProviderCompilationPass($this->id, $this->serviceProviders, $this->usePuli, $this));
    }

    /**
     * At boot time, let's fill the container with the registry.
     */
    public function boot()
    {
        $registryServiceName = 'service_provider_registry_'.$this->id;
        $this->container->set($registryServiceName, $this->getRegistry($this->container));
    }

    /**
     * @param ContainerInterface $container
     * @return Registry
     * @throws InvalidArgumentException
     */
    public function getRegistry(ContainerInterface $container)
    {
        $discovery = null;
        if ($this->usePuli) {
            $discovery = $this->getPuliDiscovery($container);
        }

        // In parallel, let's merge the registry:
        $registry = new Registry($this->serviceProviders, $discovery);
        return $registry;
    }

    /**
     * @param ContainerInterface $container
     * @return Discovery
     * @throws InvalidArgumentException
     */
    protected function getPuliDiscovery(ContainerInterface $container)
    {
        if (!$container->has('puli.discovery')) {
            throw new InvalidArgumentException('Could not find puli.discovery in container. Make sure you add the Puli bundle to your AppKernel.php file. Alternatively, you can disable Puli detection by passing false as the second argument to the InteropServiceProviderBridgeBundle.');
        }

        return $container->get('puli.discovery');
    }
}
