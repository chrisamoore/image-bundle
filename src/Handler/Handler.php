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
 * Interface ImageHandler
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 */
interface Handler
{
    /**
     * @param string  $originalFile
     * @param integer $width
     * @param integer $height
     * @param Boolean $throwException
     * @param string  $fallbackImage
     */
    public function __construct($originalFile, $width, $height, $throwException, $fallbackImage);

    /**
     * Defines the callback to call to compute the new filename
     *
     * @param \Closure $fileCallback
     */
    public function setFileCallback($fileCallback);

    /**
     * When processing the filename, call the callback
     *
     * @param string $filename
     */
    public function getFilename($filename);

    /**
     * @param string  $file
     * @param string  $type
     * @param integer $quality
     *
     * @return bool|string
     */
    public function save($file, $type = 'guess', $quality = 80);
}
