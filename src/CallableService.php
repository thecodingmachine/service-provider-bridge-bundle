<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This is a class that is callable (through the __invoke method).
 * When invoke is called, it resolves the service in the service container.
 */
class CallableService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * Constructs the object that will resolve and return the $serviceName service
     * on __invoke.
     *
     * @param ContainerInterface $container
     * @param string $serviceName
     */
    public function __construct(ContainerInterface $container, $serviceName)
    {
        $this->container = $container;
        $this->serviceName = $serviceName;
    }

    /**
     * Returns the $serviceName service.
     *
     * @return object
     */
    public function __invoke()
    {
        return $this->container->get($this->serviceName);
    }
}
