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


namespace Uecode\Bundle\ImageBundle\Services;

use Gregwar\Image\Image;

/**
 * Class ImageHandler
 *
 * Based on Gregwar's Image Repo { https://github.com/Gregwar/Image }
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
class ImageHandler extends Image
{
    /**
     * @var null
     */
    protected $fileCallback = null;

    /**
     * @param null $originalFile
     * @param null $width
     * @param null $height
     * @param bool $throwException
     * @param null $fallbackImage
     */
    public function __construct(
        $originalFile = null,
        $width = null,
        $height = null,
        $throwException = null,
        $fallbackImage = null
    ){
        $this->useFallback(!$throwException);
        $this->setFallback($fallbackImage);

        parent::__construct($originalFile, $width, $height);
    }


    /**
     * Defines the callback to call to compute the new filename
     */
    public function setFileCallback($fileCallback)
    {
        $this->fileCallback = $fileCallback;
    }

    /**
     * When processing the filename, call the callback
     */
    protected function getFilename($filename)
    {
        $callback = $this->fileCallback;

        if (null === $callback || substr($filename, 0, 1) == '/') {
            return $filename;
        }

        return $callback($filename);
    }

    /**
     * @param        $file
     * @param string $type
     * @param int    $quality
     *
     * @return bool|string
     */
    public function save($file, $type = 'guess', $quality = 80)
    {
        return parent::save($file, $type, $quality);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }
}
