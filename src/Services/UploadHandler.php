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

use Aws\Common\Aws;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var string $tmpDir
     */
    public $tmpDir;

    /**
     * @var string $directory
     */
    public $directory;

    /**
     * @var string|boolean $uploadDir
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
     * @var $handler
     */
    public $handler;

    /**
     * @var \Aws\S3\S3Client $s3
     */
    public $s3;

    /**
     * @var string $path
     */
    public $path;

    /**
     * @var string $provider
     */
    public $provider;

    /**
     * @var \stdClass $meta
     */
    public $meta;

    /**
     * @var string $name
     */
    public $name;

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Symfony\Component\HttpFoundation\RequestStack $request
     * @param \Symfony\Component\Filesystem\Filesystem                                                 $filesystem
     * @param                                                                                          $rootDir
     * @param                                                                                          $tmpDir
     * @param                                                                                          $uploadDir
     *
     * @param ImageService                                                                             $handler
     * @param                                                                                          $provider
     *
     * @param                                                                                          $s3Client
     * @param                                                                                          $bucket
     * @param                                                                                          $directory
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
        $provider,
        $s3Client,
        $bucket,
        $directory
    ){
        $this->request    = $request;
        $this->fileSystem = $filesystem;
        $this->path       = $rootDir . '/../web' . DIRECTORY_SEPARATOR . 'bundles/uecode_image/';
        $this->tmpDir     = $this->path . $tmpDir;
        $this->uploadDir  = (!$uploadDir) ? false : $this->path . $uploadDir;
        $this->handler    = $handler;
        $this->provider   = $provider;

        $this->makeDir($this->tmpDir);
        (!$this->uploadDir) ? : $this->makeDir($this->uploadDir);

        $files = $this->request->files->get('files');

        foreach ($files as $file) {
            $this->files[$file->getClientOriginalName()] = $file;
        }

        $this->file       = $this->files[$files[0]->getClientOriginalName()];
        $operations       = json_decode($this->request->get('operations'));
        $this->meta       = $operations->meta;
        $this->operations = $operations->operations;
        $this->name       = $this->name($this->file);

        $this->moveToFileSystem($this->file, $this->tmpDir);
        $this->localFile = $this->tmpDir . DIRECTORY_SEPARATOR . $this->name;
        $this->initS3($s3Client, $bucket, $directory);
    }

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function upload()
    {
        $this->handleOperations($this->operations);
        $data        = [];
        // grab all files in tmp dir and upload to each location
        $files = preg_grep('/' . explode('.', $this->name)[0] . '/', scandir($this->tmpDir . '/'));

        foreach ($files as $file) {
            $file     = $this->tmpDir . DIRECTORY_SEPARATOR . $file;
            $filename = explode('/', $file);
            $filename = end($filename);

            switch ($this->provider) {
                case 's3':
                    $data['s3'][$filename] = $this->handleS3Upload($file, $this->request);
                    break;
                case 'local':
                    $this->fileSystem->copy($file, $this->uploadDir . DIRECTORY_SEPARATOR . $filename);
                    $data['local'][$filename] = $this->url() . $filename;
                    break;
            }
        }
        return $data;
    }

    /**
     * Cleans out Tmp Dir
     *
     * @codeCoverageIgnore
     */
    public function cleanTmp()
    {
        foreach (scandir($this->tmpDir . '/') as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            unlink($this->tmpDir . DIRECTORY_SEPARATOR . $file);
        }
        return true;
    }

    /**
     * @param string $operations
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function handleOperations($operations)
    {
        $ops = [];
        foreach ($operations as $operation) {
            $ops[] = $this->doOperation($operation);
        }
        return $ops;
    }

    /**
     * Interprets Gregwar Image API
     *
     * @codeCoverageIgnore
     */
    protected function doOperation($operation)
    {
        $file   = $this->handler->open($this->localFile);
        $opName = 'n/a';
        $ops    = [];
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

            $ops          = [];
            $ops[$opName] = $this->toUrl($opName . $this->name);
        }
        return $ops;
    }

    /**
     * @param string $file
     * @param string $path
     *
     * @internal param null|string $filename
     *
     * @return string
     */
    protected function moveToFileSystem($file, $path)
    {
        $this->fileSystem->copy($file, $path . DIRECTORY_SEPARATOR . $this->name);

        return $this->toUrl($this->name);
    }

    /**
     * @param string $filename
     *
     * @return string
     */
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
        $url = ($this->request->isSecure()) ? 'https://' : 'http://';
        $url .= $this->request->getBaseUrl() . $this->request->getHost();
        $url .= '/bundles/uecode_image/';
        return $url;
    }

    /**
     * @param string $dir
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
     */
    protected function initS3($s3Client, $bucket, $directory)
    {
        $this->s3        = $s3Client;
        $this->bucket    = $bucket;
        $this->directory = $directory;
        $this->location .=
            'https://s3.amazonaws.com/' .
            DIRECTORY_SEPARATOR .
            $this->bucket .
            DIRECTORY_SEPARATOR .
            $this->directory .
            DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $filepath
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected function handleS3Upload($filepath)
    {
        try{
            $uploaded = $this->s3->putObject(
                                 [
                                     'Bucket' => $this->bucket . DIRECTORY_SEPARATOR . $this->directory,
                                     'Key'    => $this->name,
                                     'Body'   => fopen($filepath, 'r'),
                                     'ACL'    => 'public-read',
                                 ]
            );

            return $uploaded['ObjectURL'];
        }catch (S3Exception $e){
            echo "There was an error uploading the file.\n";
        }
    }

    /**
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return string
     */
    public function name(UploadedFile $file)
    {
        $ext  = $file->guessExtension();
        $hash = md5(uniqid(time() . '_' . mt_rand(1, posix_times()['ticks']) . '_')) . '.' . $ext;
        return $hash;
    }
}