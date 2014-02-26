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

namespace Uecode\Bundle\ImageBundle\Tests\Services;

use org\bovigo\vfs\vfsStream;
use Uecode\Bundle\ImageBundle\Tests\Example;

/**
 * Class UploaderTest
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>
 */
class UploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environmemt
     */
    public function setUp()
    {
        $this->root = vfsStream::setup('exampleDir');
    }

    /**
     * test that the directory is created
     */
    public function testDirectoryIsCreated()
    {
        $example = new Example( 'id' );
        $this->assertFalse($this->root->hasChild('id'));
        $example->setDirectory(vfsStream::url('exampleDir'));
        $this->assertTrue($this->root->hasChild('id'));
    }
}
