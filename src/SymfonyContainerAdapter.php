<?php

namespace TheCodingMachine\Interop\ServiceProviderBridgeBundle;

use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\ContainerException as BridgeContainerException;
use TheCodingMachine\Interop\ServiceProviderBridgeBundle\Exception\NotFoundException as BridgeNotFoundException;
use Interop\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException as SymfonyNotFoundException;

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
        try {
            return $this->container->get($id);
        } catch (SymfonyNotFoundException $prev) {
            throw BridgeNotFoundException::fromPrevious($id, $prev);
        } catch (\Exception $prev) {
            throw BridgeContainerException::fromPrevious($id, $prev);
        }
    }

    public function has($id)
    {
        return $this->container->has($id) || $this->container->hasParameter($id);
    }
}
