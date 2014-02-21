<?php
namespace Uecode\Bundle\Controller;

use Aws\S3\Exception\S3Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UploaderController extends Controller{

    public $file;

    public $fileName;

    protected $path;
    protected $name;
    protected $stuff;

    protected function __construct()
    {
        $this->tmp_dir = $this->container->getParameter('uecode_image.tmp_dir');
        $this->upload_dir = $this->container->getParameter('uecode_image.upload_dir');
    }

    public function upload()
    {
        $request = $this->container->get('request');
        $this->file = $request->files->get('file');
        $this->operations = $request->request->get('operations');
        $this->fileName = 'filename';
        // move to tmp dir
        $this->moveToFileSystem($this->file);
        // If S3
        $this->handleS3Upload($this->file, $request);
    }

    protected function moveToFileSystem($file)
    {

    }

    protected function initS3()
    {
        $this->s3 = $this->container->get('uecode_image.provider.aws');
        $this->bucket = $this->container->getParameter('aws.s3.bucket');
        $this->directory = $this->container->getParameter('aws.s3.directory');
        $this->baseUrl = 'https://s3.amazonaws.com/';
        $this->location .= $this->bucket . '/' . $this->directory . '/';
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
