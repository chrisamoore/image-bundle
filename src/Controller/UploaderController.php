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

namespace Uecode\Bundle\ImageBundle\Controller;

use Aws\S3\Exception\S3Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UploaderController
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
class UploaderController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function uploadAction()
    {
        $uploader = $this->container->get('uecode_image.upload_handler');
        $data     = $uploader->upload();

        return new JsonResponse( $data );
    }
}
