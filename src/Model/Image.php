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

namespace Uecode\Bundle\ImageBundle\Model;

/**
 * Class Image
 *
 * @package Uecode\Bundle\ImageBundle\Model
 * @author  Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
class Image implements ImageInterface
{
    /**
     * @var
     */
    protected $url;

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $hash;

    /**
     * setUrl
     *
     * @param $url
     *
     * @internal param $type
     *
     * @return void [object]
     * @access   public
     */
    public function setUrl($url)
    {
        // TODO: Implement setUrl() method.
    }

    /**
     * getUrl()
     *
     * @internal param $type
     *
     * @return void [object]
     * @access   public
     *
     */
    public function getUrl()
    {
        // TODO: Implement getUrl() method.
    }

    /**
     * setName
     *
     * @param $name
     *
     * @internal param $type
     *
     * @return void [object]@access public
     */
    public function setName($name)
    {
        // TODO: Implement setName() method.
    }

    /**
     * getName returns all images of that name
     *
     * @internal param $type
     *
     * @return void [object]
     * @access   public
     *
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * setHash
     *
     * @param $hash
     *
     * @internal param $type
     *
     * @return void [object]@access public
     */
    public function setHash($hash)
    {
        // TODO: Implement setHash() method.
    }

    /**
     * getHash returns all images of that hash
     *
     * @internal param $type
     *
     * @return void [object]@access public
     *
     */
    public function getHash()
    {
        // TODO: Implement getHash() method.
    }
}