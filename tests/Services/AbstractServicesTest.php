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
 * @package
 * @copyright   Underground Elephant 2014
 * @license     Apache License, Version 2.0
 */

namespace Uecode\Bundle\ImageBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Uecode\Bundle\ImageBundle\Services;

/**
 * Class ImageHandlerTest
 *
 * @codeCoverageIgnore
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
abstract class AbstractServicesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $name
     * @param $object
     */
    public function instance($name, $object)
    {
        $this->assertInstanceOf($name, $object);
    }

    /**
     * @param array $attributes
     * @param       $object
     */
    public function hasAttributes(array $attributes, $object)
    {
        foreach ($attributes as $attribute => $value) {
            $this->assertObjectHasAttribute($attribute, $object);
            $this->assertAttributeEquals($value, $attribute, $object);
        }
    }

    /**
     * @param array $methods
     * @param       $object
     */
    public function hasMethods(array $methods, $object)
    {
        foreach ($methods as $method) {
            $this->assertTrue(
                 method_exists($object, $method),
                     'Class does not have method '
            );
        }
    }

    /**
     * @param       $type
     * @param array $array
     */
    public function arrayType($type, array $array)
    {
        foreach ($array as $key => $object) {
            $this->assertInstanceOf($type, $object);
        }
    }

    /**
     * @param $route
     * @param $controller
     * @param $loaderClass
     *
     * @return
     * @internal param $endpoint
     * @internal param null $test
     * @internal param string $method
     */
    public function route($route, $controller, $loaderClass)
    {
        $loader = new $loaderClass($route, $controller);

        return $loader;
    }

    /**
     * getFile
     *
     * @param type
     *
     * @return [object]
     * @throws exceptionclass [description]
     *
     * @access public
     *
     * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
     */
    public function getFile()
    {
        $file = tempnam('/tmp', 'test'); // create file
        $name = explode('/', $file);
        $filename = end($name) . '.jpg';
        return (object) [
            'location' => '/tmp/' . $filename,
            'name' => $filename
        ];
    }
}