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
    public $upload_dir;
    public $request;
    public $operations;
    public $files = [];
    protected $path;
    protected $name;
    protected $stuff;

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
        $this->file = $this->files[$files[0]->getClientOriginalName()];

        $this->operations = json_decode($this->request->get('operations'));

        $this->fs = new Filesystem();
        $this->name = $this->name($this->file);

        $this->path = $this->request->server->get('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'bundles/uecode_image/';
        $this->tmp_dir = $this->path . $this->container->getParameter('uecode_image.tmp_dir');
        $this->upload_dir = (!$this->container->getParameter('uecode_image.upload_dir')) ? false :
            $this->path . $this->container->getParameter('uecode_image.upload_dir');

        $this->makeDir($this->tmp_dir);
        $this->moveToFileSystem($this->tmp_dir, $this->file);

        // If no upload dir Don't do it
        if($this->upload_dir){
            $this->makeDir($this->upload_dir);
        }

        // Handle Provider
        switch($this->container->getParameter('uecode_image.provider')){
            case 's3':
                $data = $this->handleS3Upload($this->file, $this->request);
                break;
            case 'local':
                $data = $this->moveToFileSystem($this->upload_dir, $this->file);
                break;
        }

        $this->handleOperations();
        return new JsonResponse($data);

    }

    protected function handleOperations(){
        foreach($this->operations as $operation){
            dd($operation);
        }
    }

    protected function moveToFileSystem($path, UploadedFile $file, $filename = null)
    {
        $name = $this->name;

        $this->fs->copy($file, $path . DIRECTORY_SEPARATOR . $name);
        $web = explode('web/', $path);

        $request = $this->request;
        $http = ($request->isSecure()) ? 'https://' : 'http://';
        return $http . $request->getBaseUrl() . $request->getHost() . DIRECTORY_SEPARATOR . $web[1] . DIRECTORY_SEPARATOR . $name;
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

    //TODO: Solid Principle needs more love here
    protected function handleS3Upload($filepath, Request $request)
    {
        $this->initS3();
        try {
            $uploaded = $this->s3->putObject([
                'Bucket' => $this->bucket . DIRECTORY_SEPARATOR . $this->directory,
                'Key'    => $this->name,
                'Body'   => fopen($this->tmp_dir . $this->name, 'r'),
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
