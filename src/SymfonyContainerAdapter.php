<?php

namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

/**
 * An adapter from a Symfony Container to the standardized ContainerInterface
 * Heavily adapter from Acclimate's SymfonyContainerAdapter
 */
class SymfonyContainerAdapter implements ContainerInterface
{
    /**
     * @var SymfonyContainerInterface A Symfony Container
     */
    private $container;

    /**
     * @param SymfonyContainerInterface $container A Symfony Container
     */
    public function __construct(SymfonyContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get($id)
    {
        // First, let's test if there is a parameter (parameters and services are the same thing in container/interop)
        if ($this->container->hasParameter($id)) {
            return $this->container->getParameter($id);
        }

        return $this->container->get($id);
    }

    public function has($id)
    {
        return $this->container->has($id) || $this->container->hasParameter($id);
    }
}
