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

namespace Uecode\Bundle\ImageBundle\Tests\Handler;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Uecode\Bundle\ImageBundle\Services\ImageService;
use Uecode\Bundle\ImageBundle\Services\UploadHandler;
use Uecode\Bundle\ImageBundle\Tests\Services\AbstractServicesTest;
use Uecode\Bundle\ImageBundle\Tests\Services\iServiceTest;

/**
 * Class UploadHandlerTest
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
class UploadHandlerTest extends AbstractServicesTest implements iServiceTest
{

    /**
     * @return mixed
     */
    public function getObject()
    {
        $imgService = new ImageService(
            'Uecode\\Bundle\\ImageBundle\\Handler\\ImageHandler',
            true,
            'some.jpg'
        );

        $this->file = tempnam('/tmp', 'test'); // create file

        $name = explode('/', $this->file);
        $this->fileName = end($name);

        $this->image = new UploadedFile(
            $this->file,
            $this->fileName
        );

        $json = '{
                  "operations": [
                    {
                      "resize": {
                        "width": 20,
                        "height": 20
                      },
                      "rotate": {
                        "degrees": 90
                      },
                      "crop": {
                        "x": 0,
                        "y": 0,
                        "w": 10,
                        "h": 10
                      }
                    },
                    {
                      "resize": {
                        "width": 20,
                        "height": 20
                      }
                    }
                  ],
                  "meta": {
                    "name": "pun.jpg",
                    "tags": [
                      "foo",
                      "bar",
                      "baz"
                    ],
                    "user": {
                      "id": 1,
                      "company": 1
                    }
                  }
                }';
        $request = new Request();
        $request->files->add(['files' => [$this->image]]);
        $request->attributes->add(['operations' => $json]);

        return (object) [
            'name' => 'Uecode\\Bundle\\ImageBundle\\Services\\UploadHandler',
            'request' => $request,
            'fileSystem' => new Filesystem,
            'rootDir' => '/vagrant/Symfony',
            'tmpDir' => 'tmp',
            'uploadDir' => 'upload',
            'handler' => $imgService,
            'provider' => 'local'
        ];
    }


    /** @test */
    public function runTest()
    {
        $construct = $this->getObject();
        $object    = new UploadHandler(
            $construct->request,
            $construct->fileSystem,
            $construct->rootDir,
            $construct->tmpDir,
            $construct->uploadDir,
            $construct->handler,
            $construct->provider
        );

        $attributes = [
            'root' => '/vagrant/Symfony',
            'path' => '/vagrant/Symfony/../web/bundles/uecode_image/',
            'tmpDir' => '/vagrant/Symfony/../web/bundles/uecode_image/tmp',
            'uploadDir' =>  '/vagrant/Symfony/../web/bundles/uecode_image/upload',
            'provider' => 'local',
        ];

        $methods = [
            'upload',
            'cleanTmp',
            'handleOperations',
            'doOperation',
            'moveToFileSystem',
            'toUrl',
            'url',
            'makeDir',
            'initS3',
            'handleS3Upload',
            'name'
        ];

        $this->assertEquals(20, $object->operations[0]->resize->width);
        $this->assertEquals(20, $object->operations[0]->resize->height);
        $this->assertEquals(90, $object->operations[0]->rotate->degrees);
        $this->assertEquals(0, $object->operations[0]->crop->x);
        $this->assertEquals(0, $object->operations[0]->crop->y);
        $this->assertEquals(10, $object->operations[0]->crop->w);
        $this->assertEquals(10, $object->operations[0]->crop->h);

        $this->assertEquals(20, $object->operations[1]->resize->width);
        $this->assertEquals(20, $object->operations[1]->resize->height);

        $this->assertEquals("pun.jpg", $object->meta->name);
        $this->assertEquals(1, $object->meta->user->company);
        $this->assertEquals(1, $object->meta->user->id);


        $this->assertTrue( (boolean) preg_match('/' . $object->name . '/', $object->localFile));
        $this->assertInstanceOf('Symfony\\Component\\Filesystem\\Filesystem', $object->fileSystem);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Request', $object->request);
        $this->assertInstanceOf('Uecode\\Bundle\\ImageBundle\\Services\\ImageService', $object->handler);
        $this->arrayType('Symfony\\Component\\HttpFoundation\\File\\UploadedFile', $object->files);

        $this->instance($construct->name, $object);
        $this->hasAttributes($attributes, $object);
        $this->hasMethods($methods, $object);

        $object->cleanTmp();
    }
}
