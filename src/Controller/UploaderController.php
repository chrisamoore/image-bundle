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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class UploaderController
 *
 * @author Chris Moore <chrisamoore@gmail.com
 */
class UploaderController extends Controller
{
    /**
     * @var Resource $file
     */
    public $file;

    /**
     * @var string FileName
     */
    public $fileName;

    /**
     * @var Filesystem $fs
     */
    public $fs;

    /**
     * @var string $location
     */
    public $location;

    /**
     * @var string $bucket
     */
    public $bucket;

    /**
     * @var string $baseUrl
     */
    public $baseUrl;

    /**
     * @var string $tmpDir
     */
    public $tmpDir;

    /**
     * @var string $directory
     */
    public $directory;

    /**
     * @var string $uploadDir
     */
    public $uploadDir;

    /**
     * @var Request $request
     */
    public $request;

    /**
     * @var string $operations
     */
    public $operations;

    /**
     * @var array $files
     */
    public $files = [];

    /**
     * @var string $localFile
     */
    public $localFile;

    /**
     * @var string $path
     */
    protected $path;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $stuff
     */
    protected $stuff;

    /**
     * @return JsonResponse
     */
    public function uploadAction()
    {
        $this->request = $this->container->get('request');

        $this->request->files->get('files');

        // Preface for MultiFile upload
        $files = $this->request->files->get('files');
        foreach ($files as $file) {
            $this->files[$file->getClientOriginalName()] = $file;
        }
        // Temp fix
        $this->file       = $this->files[$files[0]->getClientOriginalName()];
        $operations       = json_decode($this->request->get('operations'));
        $this->meta       = $operations->meta;
        $this->operations = $operations->operations;

        $this->fs   = new Filesystem();
        $this->name = $this->name($this->file);

        $this->path      = $this->request->server->get('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'bundles/uecode_image/';
        $this->tempDir   = $this->path . $this->container->getParameter('uecode_image.tempDir');
        $this->uploadDir =
            (!$this->container->getParameter('uecode_image.uploadDir')) ? false :
                $this->path . $this->container->getParameter('uecode_image.uploadDir');

        $this->makeDir($this->tempDir);
        $this->moveToFileSystem($this->file, $this->tempDir);
        $this->localFile = $this->tempDir . DIRECTORY_SEPARATOR . $this->name;

        // If no upload dir Don't do it
        if ($this->uploadDir) {
            $this->makeDir($this->uploadDir);
        }

        $data = $this->upload();

        return new JsonResponse($data);
    }

    /**
     * @return mixed
     */
    protected function upload()
    {
        $data['ops'] = $this->handleOperations();

        // grab all files in tmp dir and upload to each location
        $files = preg_grep('/' . explode('.', $this->name)[0] . '/', scandir($this->tempDir . '/'));

        foreach ($files as $file) {
            $file     = $this->tempDir . DIRECTORY_SEPARATOR . $file;
            $filename = explode('/', $file);

            switch ($this->container->getParameter('uecode_image.provider')) {
                case 's3':
                    $data['s3'] = $this->handleS3Upload($file, $this->request);
                    break;
                case 'local':
                    $data['local'] = $this->fs->copy($file, $this->uploadDir . DIRECTORY_SEPARATOR . end($filename));
                    break;
            }
        }
        $this->cleanTmp();

        return $data;
    }

    /**
     *
     */
    protected function cleanTmp()
    {
        foreach (scandir($this->tempDir . '/') as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            unlink($this->tempDir . DIRECTORY_SEPARATOR . $file);
        }
    }

    /**
     * @return array
     */
    protected function handleOperations()
    {
        foreach ($this->operations as $operation) {
            $ops[] = $this->doOperation($operation);
        }
        return $ops;
    }

    /**
     * Interprets Gregwar Image API
     */
    protected function doOperation($operation)
    {
        $handler = $this->get('image.handling');
        $file    = $handler->open($this->localFile);
        foreach ($operation as $op => $params) {
            switch ($op) {
                case 'resize':
                    $file->resize($params->width, $params->height);
                    break;
                case 'rotate':
                    $file->rotate($params->degrees);
                    break;
                case 'crop':
                    $file->crop($params->x, $params->y, $params->w, $params->h);
                    break;
            }
            $ops[] = $file->save($this->tempDir . DIRECTORY_SEPARATOR . $op . '_' . $this->name, 'jpg', 100);
        }
        return $ops;
    }

    /**
     * @param string      $file
     * @param string      $path
     * @param null|string $filename
     *
     * @return string
     */
    protected function moveToFileSystem($file, $path, $filename = null)
    {
        $name = $this->name;

        $this->fs->copy($file, $path . DIRECTORY_SEPARATOR . $name);
        $web = explode('web/', $path);

        $request = $this->request;
        $http    = ($request->isSecure()) ? 'https://' : 'http://';
        return
            $http . $request->getBaseUrl() . $request->getHost() . DIRECTORY_SEPARATOR .
            $web[1] .
            DIRECTORY_SEPARATOR .
            $name;
    }

    /**
     * @param string $dir
     */
    protected function makeDir($dir)
    {
        if (!$this->fs->exists($dir)) {
            try {
                $this->fs->mkdir($dir);
            } catch (IOException $e) {
                echo "An error occurred while creating your directory";
            }
        }
    }

    /**
     *
     */
    protected function initS3()
    {
        $this->s3        = $this->container->get('uecode_image.provider.aws');
        $this->bucket    = $this->container->getParameter('aws.s3.bucket');
        $this->directory = $this->container->getParameter('aws.s3.directory');
        $this->baseUrl   = 'https://s3.amazonaws.com/';
        $this->location .=
            $this->baseUrl .
            DIRECTORY_SEPARATOR .
            $this->bucket .
            DIRECTORY_SEPARATOR .
            $this->directory .
            DIRECTORY_SEPARATOR;
    }

    /**
     *
     * @param string  $filepath
     * @param Request $request
     *
     * @return string
     */
    protected function handleS3Upload($filepath, Request $request)
    {
        $this->initS3();
        try {
            $uploaded = $this->s3->putObject(
                [
                    'Bucket' => $this->bucket . DIRECTORY_SEPARATOR . $this->directory,
                    'Key'    => $this->name,
                    'Body'   => fopen($filepath, 'r'),
                    'ACL'    => 'public-read',
                ]
            );

            return $uploaded['ObjectURL'];
        } catch (S3Exception $e) {
            echo "There was an error uploading the file.\n";
        }
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function name($file)
    {
        $ext  = $file->guessExtension();
        $hash = md5(uniqid(time() . '_' . mt_rand(1, posix_times()['ticks']) . '_')) . '.' . $ext;
        return $hash;
    }
}
