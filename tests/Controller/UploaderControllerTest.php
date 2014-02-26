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

namespace Uecode\Bundle\ImageBundle\Tests\Controller;

use Uecode\Bundle\ImageBundle\Controller\UploaderController;
use Uecode\Bundle\ImageBundle\Tests\Services\AbstractServicesTest;
use Uecode\Bundle\ImageBundle\Tests\Services\iServiceTest;

/**
 * Class ImageServiceTest
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
class UploaderControllerTest extends AbstractServicesTest implements iServiceTest
{

    /**
     * @return object
     */
    public function getObject()
    {
        return (object) [
            'name'           => 'Uecode\\Bundle\\ImageBundle\\Controller\\UploaderController',
        ];
    }

    /** @test */
    public function runTest()
    {
        $construct = $this->getObject();
        $object = new UploaderController;

        $attributes = [
//            'handlerClass'   => $construct->handler,
//            'throwException' => $construct->throwException,
//            'fallbackImage'  => $construct->fallbackImage
        ];

        $methods = [
            'uploadAction'
        ];

        $this->instance($construct->name, $object);
        $this->hasAttributes($attributes, $object);
        $this->hasMethods($methods, $object);
    }
}