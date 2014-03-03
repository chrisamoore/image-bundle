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
 * Interface ImageInterface
 *
 * @author  Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 *
 * @package Uecode\Bundle\ImageBundle\Model
 */
interface ImageInterface
{

    /**
     * setUrl
     *
     * @param type
     *
     * @return [object]
     *
     * @access public
     *
     */
    public function setUrl($url);

    /**
     * getUrl()
     *
     * @internal param $type
     *
     * @return void [object]@access public
     *
     */
    public function getUrl();

    /**
     * setName
     *
     * @param type
     *
     * @return [object]
     * @access public
     *
     */
    public function setName($name);

    /**
     * getName returns all images of that name
     *
     * @internal param $type
     *
     * @return void [object]
     * @access   public
     *
     */
    public function getName();

    /**
     * setHash
     *
     * @param type
     *
     * @return [object]
     *
     * @access public
     *
     */
    public function setHash($hash);

    /**
     * getHash returns all images of that hash
     *
     * @internal param $type
     *
     * @return void [object]@access public
     *
     */
    public function getHash();
}