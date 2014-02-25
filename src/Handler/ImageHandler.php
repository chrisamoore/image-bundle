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

namespace Uecode\Bundle\ImageBundle\Handler;

use Gregwar\Image\Image;

/**
 * Image manipulation class
 *
 * Based on Gregwar's Image Repository
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class ImageHandler extends Image implements Handler
{
    /**
     * @var \Closure|null
     */
    protected $fileCallback = null;

    /**
     * {@inheritDoc}
     */
    public function __construct($originalFile, $width, $height, $throwException, $fallbackImage) {
        $this->useFallback(!$throwException);
        $this->setFallback($fallbackImage);

        parent::__construct($originalFile, $width, $height);
    }

    /**
     * {@inheritDoc}
     */
    public function setFileCallback($fileCallback)
    {
        $this->fileCallback = $fileCallback;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilename($filename)
    {
        $callback = $this->fileCallback;

        if (null === $callback || substr($filename, 0, 1) == '/') {
            return $filename;
        }

        return $callback($filename);
    }

    /**
     * {@inheritDoc}
     */
    public function save($file, $type = 'guess', $quality = 80)
    {
        return parent::save($file, $type, $quality);
    }
}
