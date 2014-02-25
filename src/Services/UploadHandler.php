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

use Aws\S3\Exception\S3Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UploaderHandler
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>, <cmoore@undergroundelephant.com>
 */
class UploadHandler
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
     * @var Filesystem $fileSystem
     */
    public $fileSystem;

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
    public $files = [ ];

    /**
     * @var string $localFile
     */
    public $localFile;

    /**
     * @var $handler
     */
    public $handler;

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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Filesystem\Filesystem  $filesystem
     * @param                                           $rootDir
     * @param                                           $tmpDir
     * @param                                           $uploadDir
     *
     * @param ImageService                              $handler
     * @param                                           $provider
     *
     * @todo Inject Model
     */
    public function __construct(
        Request $request,
        Filesystem $filesystem,
        $rootDir,
        $tmpDir,
        $uploadDir,
        ImageService $handler,
        $provider
    ){
        $this->request    = $request;
        $this->fileSystem = $filesystem;
        $this->root       = $rootDir;
        $this->path       = $this->root . '/../web' . DIRECTORY_SEPARATOR . 'bundles/uecode_image/';
        $this->tmpDir     = $this->path . $tmpDir;
        $this->uploadDir  = ( !$uploadDir ) ? false : $this->path . $uploadDir;
        $this->handler    = $handler;
        $this->provider   = $provider;

        $this->makeDir($this->tmpDir);
        ( !$this->uploadDir ) ? : $this->makeDir($this->uploadDir);

        $files = $this->request->files->get('files');
        foreach ($files as $file) {
            $this->files[ $file->getClientOriginalName() ] = $file;
        }

        $this->file       = $this->files[ $files[ 0 ]->getClientOriginalName() ];
        $operations       = json_decode($this->request->get('operations'));
        $this->meta       = $operations->meta;
        $this->operations = $operations->operations;
        $this->name       = $this->name($this->file);

        $this->moveToFileSystem($this->file, $this->tmpDir);
        $this->localFile = $this->tmpDir . DIRECTORY_SEPARATOR . $this->name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function upload()
    {
        $data[ 'ops' ] = $this->handleOperations($this->operations);

        // grab all files in tmp dir and upload to each location
        $files = preg_grep('/' . explode('.', $this->name)[ 0 ] . '/', scandir($this->tmpDir . '/'));

        foreach ($files as $file) {
            $file     = $this->tmpDir . DIRECTORY_SEPARATOR . $file;
            $filename = explode('/', $file);
            $filename = end($filename);

            switch ($this->provider) {
                case 's3':
                    $data[ 's3' ] = $this->handleS3Upload($file, $this->request);
                    break;
                case 'local':
                    $this->fileSystem->copy($file, $this->uploadDir . DIRECTORY_SEPARATOR . $filename);
                    $data[ 'local' ] = $this->url() . $filename;
                    break;
            }
        }
        $this->cleanTmp();

        return $data;
    }

    /**
     * Cleans out Tmp Dir
     */
    protected function cleanTmp()
    {
        foreach (scandir($this->tmpDir . '/') as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            unlink($this->tmpDir . DIRECTORY_SEPARATOR . $file);
        }
    }

    /**
     * @param $operations
     *
     * @return array
     */
    protected function handleOperations($operations)
    {
        foreach ($operations as $operation) {
            $ops[ ] = $this->doOperation($operation);
        }
        return $ops;
    }

    /**
     * Interprets Gregwar Image API
     */
    protected function doOperation($operation)
    {
        $file   = $this->handler->open($this->localFile);
        $opName = 'n/a';
        foreach ($operation as $op => $params) {
            switch ($op) {
                case 'resize':
                    $file->resize($params->width, $params->height);
                    $opName = $op . '_' . $params->width . 'x' . $params->height . '_';
                    break;
                case 'rotate':
                    $file->rotate($params->degrees);
                    $opName = $op . '_' . $params->degrees . '_';
                    break;
                case 'crop':
                    $file->crop($params->x, $params->y, $params->w, $params->h);
                    $opName = $op . '_' . $params->x . ',' . $params->y . '_' . $params->w . 'x' . $params->h . '_';
                    break;
            }
            $file->save($this->tmpDir . DIRECTORY_SEPARATOR . $opName . $this->name, 'jpg', 100);
            $ops[ $opName ] = $this->toUrl($opName . $this->name);
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
        $this->fileSystem->copy($file, $path . DIRECTORY_SEPARATOR . $this->name);

        return $this->toUrl($this->name);
    }

    public function toUrl($filename)
    {
        $parts = explode('/uecode_image/', $this->uploadDir);
        return $this->url() . end($parts) . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @internal param $name
     *
     * @return string
     */
    protected function url()
    {
        $url = ( $this->request->isSecure() ) ? 'https://' : 'http://';
        $url .= $this->request->getBaseUrl() . $this->request->getHost();
        $url .= '/bundles/uecode_image/';
        return $url;
    }

    /**
     * @param string $dir
     */
    protected function makeDir($dir)
    {
        if (!$this->fileSystem->exists($dir)) {
            try{
                $this->fileSystem->mkdir($dir);
            }catch (IOException $e){
                echo "An error occurred while creating your directory";
            }
        }
    }

    /**
     * Sets up S3
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
        try{
            $uploaded = $this->s3->putObject(
                                 [
                                     'Bucket' => $this->bucket . DIRECTORY_SEPARATOR . $this->directory,
                                     'Key'    => $this->name,
                                     'Body'   => fopen($filepath, 'r'),
                                     'ACL'    => 'public-read',
                                 ]
            );

            return $uploaded[ 'ObjectURL' ];
        }catch (S3Exception $e){
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
        $hash = md5(uniqid(time() . '_' . mt_rand(1, posix_times()[ 'ticks' ]) . '_')) . '.' . $ext;
        return $hash;
    }
}