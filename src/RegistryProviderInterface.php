<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\InvalidArgumentException;
use TheCodingMachine\ServiceProvider\Registry;

/**
 * Provides a service provider registry.
 */
interface RegistryProviderInterface
{
    /**
     * @param ContainerInterface $container
     * @return Registry
     * @throws InvalidArgumentException
     */
    public function getRegistry(ContainerInterface $container);
}