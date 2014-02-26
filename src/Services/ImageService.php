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

namespace Uecode\Bundle\ImageBundle\Services;

use Symfony\Component\Templating\Helper\CoreAssetsHelper;
use Uecode\Bundle\ImageBundle\Handler\ImageHandler;

/**
 * Image manipulation service
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>
 */
class ImageService
{
    /**
     * @var ImageHandler $handlerClass
     */
    private $handlerClass;

    /**
     * @var \Exception $thrownException
     */
    private $throwException;

    /**
     * @var string $fallbackImage
     */
    private $fallbackImage;

    /**
     * @param $handlerClass
     * @param $throwException
     *
     * @param $fallbackImage
     *
     *
     */
    public function __construct(
        $handlerClass,
        $throwException,
        $fallbackImage
    ){
        $this->handlerClass   = $handlerClass;
        $this->throwException = $throwException;
        $this->fallbackImage  = $fallbackImage;
    }

    /**
     * Get a manipulable image instance
     *
     * @param string $file the image path
     *
     * @return object a manipulable image instance available?
     *
     */
    public function open($file)
    {
        if (strlen($file) >= 1 && $file[ 0 ] === '@') {
            $file = $this->kernel->locateResource($file);
        }

        return $this->createInstance($file);
    }

    /**
     * Creates an instance defining the cache directory
     *
     * @param string       $file
     * @param null|integer $width
     * @param null|integer $height
     *
     * @return object
     */
    private function createInstance($file, $width = null, $height = null)
    {
        $handlerClass = $this->handlerClass;
        $image        = new $handlerClass( $file, $width, $height, $this->throwException, $this->fallbackImage );

        return $image;
    }
}
