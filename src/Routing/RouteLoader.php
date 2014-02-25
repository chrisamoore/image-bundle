<?php

/**
 * Copyright 2014 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     image-bundle
 * @copyright   Underground Elephant 2014
 * @license     Apache License, Version 2.0
 */

namespace Uecode\Bundle\ImageBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteLoader
 *
 * @author Chris Moore <chrisamoore@gmail.com>
 */
class RouteLoader implements LoaderInterface
{
    /**
     * @var Boolean $loaded
     */
    private $loaded = false;

    /**
     * @param string $route
     * @param string $controller
     */
    public function __construct($route, $controller)
    {
        $this->route      = $route;
        $this->controller = $controller;
    }

    /**
     * @param string      $resource
     * @param null|string $type
     *
     * @return RouteCollection
     * @throws \RuntimeException
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "' . $this->route . '" loader twice');
        }

        $routes = new RouteCollection();

        $path         = '/' . $this->route;
        $defaults     = ['_controller' => $this->controller];
        $requirements = [];
        $options      = [];
        $host         = '';
        $schemes      = [];
        $methods      = ['POST'];
        $condition    = null;

        $route = new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        // add the new route to the route collection:
        $routeName = 'uecode_image.' . $this->route;
        $routes->add($routeName, $route);

        $this->loaded = true;
        return $routes;
    }

    /**
     * @param string      $resource
     * @param null|string $type
     *
     * @return Boolean
     */
    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }

    /**
     *
     */
    public function getResolver()
    {
        // needed, but can be blank, unless you want to load other resources
        // and if you do, using the Loader base class is easier (see below)
    }

    /**
     * @param LoaderResolverInterface $resolver
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}
