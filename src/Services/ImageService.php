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

use Uecode\Bundle\ImageBundle\Handler\ImageHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Image manipulation service
 *
 * Based on Gregwar's Image Repo
 *
 * @author Chris Moore <chrisamoore@gmail.com>
 */
class ImageService
{
    /**
     * @var string $cacheDirectory
     */
    private $cacheDirectory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ImageHandler $handlerClass
     */
    private $handlerClass;

    /**
     * @var KernelInterface $kernel
     */
    private $kernel;

    /**
     * @var \Exception $thrownException
     */
    private $throwException;

    public function __construct(
        $cacheDirectory,
        ImageHandler $handlerClass,
        ContainerInterface $container,
        KernelInterface $kernel,
        $throwException
    ) {
        $this->cacheDirectory = $cacheDirectory;
        $this->handlerClass   = $handlerClass;
        $this->container      = $container;
        $this->kernel         = $kernel;
        $this->throwException = $throwException;
    }

    /**
     * Get a manipulable image instance
     *
     * @param string $file the image path
     *
     * @return object a manipulable image instance
     */
    public function open($file)
    {
        if (strlen($file) >= 1 && $file[0] === '@') {
            $file = $this->kernel->locateResource($file);
        }

        return $this->createInstance($file);
    }

    /**
     * Get a new image
     *
     * @param integer $width  The width
     * @param integer $height The height
     *
     * @return object a manipulable image instance
     */
    public function create($width, $height)
    {
        return $this->createInstance(null, $width, $height);
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
        $container = $this->container;
        $webDir    = $container->getParameter('gregwar_image.web_dir');

        $handlerClass = $this->handlerClass;
        $image        = new $handlerClass($file, $width, $height, $this->throwException);

        $image->setCacheDir($this->cacheDirectory);
        $image->setActualCacheDir($webDir . '/' . $this->cacheDirectory);

        $image->setFileCallback(
            function ($file) use ($container) {
                return $container->get('templating.helper.assets')
                    ->getUrl($file);
            }
        );

        return $image;
    }
}
