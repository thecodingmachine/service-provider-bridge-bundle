<?php
namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\InvalidArgumentException;
use TheCodingMachine\ServiceProvider\Registry;
use TheCodingMachine\ServiceProvider\RegistryInterface;

/**
 * Provides a service provider registry.
 */
interface RegistryProviderInterface
{
    /**
     * @param ContainerInterface $container
     * @return RegistryInterface
     * @throws InvalidArgumentException
     */
    public function getRegistry(ContainerInterface $container);
}