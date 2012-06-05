<?php

namespace Acme\DemoBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class RequestListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * This method is executed for every Http Request. If the content is found
     * in Apc Cache the execution flow is stopped and a Response is returned
     * inmediately, no controller action is executed.
     * @param GetResponseEvent $event
     * @return type
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // No caching for dev enviroment
        if ($this->container->getParameter('kernel.environment') === 'dev') {
            return;
        }

        $request = $event->getRequest();

        // Cahing only for Http Get method
        if ('GET' !== $request->getMethod()) {
            return;
        }

        // Build Apc cache key
        $path = 'controller.' . $request->getPathInfo();

        $qs = $request->getQueryString();

        // For caching request with query string if you do not want to cache it
        // comment out
        if (!empty($qs)) {
            $path .= '.' . sha1($qs);
        }

        if (apc_exists($path)) {
            $event->setResponse(apc_fetch($path));
        }
    }

    /**
     * This method is executed after a Controller action and before the Response
     * is returned to the Browser or to another agent. Stores the Response object,
     * If it has not been cached
     *
     * @param FilterResponseEvent $event
     * @return type
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        // No caching for dev enviroment
        if ($this->container->getParameter('kernel.environment') === 'dev') {
            return;
        }

        $request = $event->getRequest();

        // Cahing only for Http Get method
        if ('GET' !== $request->getMethod()) {
            return;
        }

        // Build Apc cache key
        $path = 'controller.' . $request->getPathInfo();

        // For caching request with query string if you do not want to cache it
        // comment out
        $qs = $request->getQueryString();

        if (!empty($qs)) {
            $path .= '.' . sha1($qs);
        }

        if (!apc_exists($path)) {
            apc_store($path, $event->getResponse());
        }
    }

    /*
     * This method prevent to cache Response when occurs Exceptions
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $event->stopPropagation();
    }
}
