<?php

namespace Uecode\Bundle\ImageBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


class RouteLoader implements LoaderInterface
{
    private $loaded = false;

    public function __construct($route, $controller)
    {
        $this->route = $route;
        $this->controller = $controller;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "' .$this->route. '" loader twice');
        }

        $routes = new RouteCollection();

        $path = '/' . $this->route;
        $defaults = ['_controller' => $this->controller];
        $requirements = [];
        $options = [];
        $host = '';
        $schemes = [];
        $methods = ['POST'];
        $condition = null;

        $route = new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition );

        // add the new route to the route collection:
        $routeName = 'uecode_image.' . $this->route;
        $routes->add($routeName, $route);

        $this->loaded = true;
        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }

    public function getResolver()
    {
        // needed, but can be blank, unless you want to load other resources
        // and if you do, using the Loader base class is easier (see below)
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}