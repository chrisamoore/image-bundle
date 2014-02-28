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
 * @package     SymfonySandbox
 * @copyright   Underground Elephant 2014
 * @license     Apache License, Version 2.0
 */

namespace Uecode\Bundle\ImageBundle\Tests\Routing;

use Symfony\Component\Config\Loader\LoaderResolver;
use Uecode\Bundle\ImageBundle\Tests\Services\AbstractServicesTest;
use Uecode\Bundle\ImageBundle\Tests\Services\iServiceTest;

/**
 * Class RouteLoaderTest
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
class RouteLoaderTest extends AbstractServicesTest implements iServiceTest
{

    /**
     * @return mixed
     */
    public function getObject()
    {
        return (object) [
            'route'      => 'upload',
            'controller' => 'Uecode\\Bundle\\ImageBundle\\Controller\\UploaderController',
            'loader'     => 'Uecode\\Bundle\\ImageBundle\\Routing\\RouteLoader'
        ];
    }

    /**
     * @test
     * runTest
     *
     * @internal param $type
     *
     * @return mixed|void
     * @access   public
     *
     * @author   Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
     */
    public function runTest()
    {
        $construct = $this->getObject();

        $object = $this->route(
            $construct->route,
            $construct->controller,
            $construct->loader
        );

        $methods = [
            'load',
            'supports',
            'getResolver',
            'setResolver'
        ];

        $attributes = [
            'loaded'     => false,
            'route'      => 'upload',
            'controller' => $construct->controller
        ];

        $this->instance($construct->loader, $object);
        $this->hasAttributes($attributes, $object);
        $this->hasMethods($methods, $object);

        $routeObj = $object->load($construct->controller);
        $routes = $routeObj->getIterator();

        $this->instance('Symfony\\Component\\Routing\\RouteCollection', $routeObj);
        // Iterate over $routes and do stuff
        foreach($routes as $key => $route) {
            $this->assertEquals('uecode_image.upload', $key);
            $this->instance('Symfony\\Component\\Routing\\Route', $route);
            $this->assertEquals('/' . $construct->route, $route->getPattern());
            $this->assertEquals('POST', $route->getRequirements()['_method']);
            $this->assertEquals('Uecode\Bundle\ImageBundle\Controller\UploaderController', $route->getDefaults()['_controller']);
        }

        $this->assertNull($object->getResolver());
        $this->assertNull($object->setResolver(new LoaderResolver([])));
        $this->assertFalse($object->supports($construct->controller));
    }
}