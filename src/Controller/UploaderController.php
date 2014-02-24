<?php
namespace Uecode\Bundle\ImageBundle\Controller;

use Aws\S3\Exception\S3Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class UploaderController extends Controller{

    public $file;
    public $fileName;
    public $fs;
    public $location;
    public $bucket;
    public $baseUrl;
    public $tmp_dir;
    public $directory;
    protected $path;
    protected $name;
    protected $stuff;

    public function uploadAction()
    {
        $this->request = $this->container->get('request');

        $this->file = $this->request->files->get('files')[0];
        $this->fs = new Filesystem();
        $this->name = $this->name($this->file);
        $this->path = $this->request->server->get('DOCUMENT_ROOT');
        $this->tmp_dir = $this->container->getParameter('uecode_image.tmp_dir');
        $this->upload_dir = $this->container->getParameter('uecode_image.upload_dir');
        $this->tmp_path = $this->path . DIRECTORY_SEPARATOR . 'bundles/uecode_image' . DIRECTORY_SEPARATOR . $this->tmp_dir . DIRECTORY_SEPARATOR;

        $this->makeDir($this->tmp_path);
        $this->moveToFileSystem($this->tmp_path, $this->file);

        // If no upload dir Don't do it
        if($this->container->getParameter('uecode_image.upload_dir')){
            $this->makeDir($this->path . DIRECTORY_SEPARATOR . $this->upload_dir);
            $this->moveToFileSystem($this->path . DIRECTORY_SEPARATOR . $this->upload_dir, $this->file);
        }

        // If S3
        if($this->container->getParameter('aws.s3') !== false)
            return new JsonResponse($this->handleS3Upload($this->file, $this->request));

        // TODO: account for not S3
    }

    protected function moveToFileSystem($path, UploadedFile $file, $filename = null)
    {
        $name = $this->name;

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
        $this->location .= $this->baseUrl . DIRECTORY_SEPARATOR . $this->bucket . DIRECTORY_SEPARATOR . $this->directory . DIRECTORY_SEPARATOR;
    }

    protected function handleS3Upload($filepath, Request $request)
    {
        $this->initS3();
        try {
            $uploaded = $this->s3->putObject([
                'Bucket' => $this->bucket . DIRECTORY_SEPARATOR . $this->directory,
                'Key'    => $this->name,
                'Body'   => fopen($this->tmp_path . DIRECTORY_SEPARATOR . $this->name, 'r'),
                'ACL'    => 'public-read',
            ]);

            return $uploaded['ObjectURL'];

        } catch (S3Exception $e) {
            echo "There was an error uploading the file.\n";
        }
    }

    public function name($file)
    {
        $ext = $file->guessExtension();
        $hash =  md5(uniqid(time() . '_' . mt_rand(1, posix_times()['ticks']) . '_')) . '.' . $ext;
        return $hash;
    }
}
