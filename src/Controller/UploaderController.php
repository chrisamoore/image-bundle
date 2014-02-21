<?php
namespace Uecode\Bundle\ImageBundle\Controller;

use Aws\S3\Exception\S3Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class UploaderController extends Controller{

    public $file;

    public $fileName;

    public $fs;

    protected $path;
    protected $name;
    protected $stuff;

    public function uploadAction()
    {
        $this->request = $this->container->get('request');

        $this->file = $this->request->files->get('files')[0];
        $this->fs = new Filesystem();

        $this->path = $this->request->server->get('DOCUMENT_ROOT');
        $this->tmp_dir = $this->container->getParameter('uecode_image.tmp_dir');
        $this->upload_dir = $this->container->getParameter('uecode_image.upload_dir');

        $this->makeDir($this->path . DIRECTORY_SEPARATOR . $this->upload_dir);
        $this->makeDir($this->path . DIRECTORY_SEPARATOR . $this->tmp_dir);

        // move to tmp dir
        $this->moveToFileSystem($this->path . DIRECTORY_SEPARATOR . $this->tmp_dir, $this->file);
        dd($this->moveToFileSystem($this->path . DIRECTORY_SEPARATOR . $this->upload_dir, $this->file));

        // If S3
//        $this->handleS3Upload($this->file, $this->request);
    }

    protected function moveToFileSystem($path, $file, $filename = null)
    {
        $name = (!$filename) ? $file->getClientOriginalName() : $filename;

        $this->fs->copy($file, $path . DIRECTORY_SEPARATOR . $name);
        $web = explode('web/', $path);

        return 'http://' . $this->request->getHost() . $web[1] . DIRECTORY_SEPARATOR . $name;
    }

    protected function makeDir($dir)
    {
        if(!$this->fs->exists($dir))
            try {
                $this->fs->mkdir($dir);
            } catch (IOException $e) {
                echo "An error occurred while creating your directory";
            }
    }

    protected function initS3()
    {
        $this->s3 = $this->container->get('uecode_image.provider.aws');
        $this->bucket = $this->container->getParameter('aws.s3.bucket');
        $this->directory = $this->container->getParameter('aws.s3.directory');
        $this->baseUrl = 'https://s3.amazonaws.com/';
        $this->location .= $this->bucket . DIRECTORY_SEPARATOR . $this->directory . DIRECTORY_SEPARATOR;
    }

    protected function handleS3Upload($filepath, Request $request)
    {
        $this->initS3();

        try {
            $uploaded = $this->s3->upload( $this->location, $this->fileName, fopen($filepath, 'r'), 'public-read');

            // https://s3.amazonaws.com/dev_stampede/companies/companies/aws/foo/28827e1e3a13bb2556ff9c5a63275ab8.jpg
            $assetUrl = $this->baseUrl . $this->location . $uploaded->getKey();

            return $assetUrl;

        } catch (S3Exception $e) {
            dd($e);
            echo "There was an error uploading the file.\n";
        }
    }
}
