<?php

namespace RocketShip\Upload;

use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\Exception\S3Exception;
use Guzzle\Http\EntityBody;
use RocketShip\Application;
use RocketShip\Configuration;
use RocketShip\UploadAdapter;

class Amazon implements UploadAdapter
{
    static private $client;
    static private $bucket;

    private $app;

    public function __construct()
    {
        if (empty(self::$client)) {
            $this->app        = Application::$instance;
            $config           = $this->app->config->uploading;
            self::$bucket     = $config->authentication->bucket;
            self::$client     = S3Client::factory([
                'key'    => $config->authentication->key,
                'secret' => $config->authentication->secret
            ]);
        }
    }

    /**
     *
     * moveObject
     *
     * Upload a file to S3 Cloud servers
     *
     * @param   string  path name to file to upload
     * @param   string  directory to use (images, files)
     * @param   string  filename to use on s3
     * @return  mixed   string: file path on success, null on error
     * @access  public
     *
     */
    public function moveObject($file, $directory, $name)
    {
        $file      = (string)$file;
        $directory = (string)$directory;
        $name      = (string)$name;

        $data = EntityBody::factory(fopen($file, 'r'));

        self::$client->putObject([
            'Bucket' => self::$bucket,
            'Key'    => $directory . '/' . $name,
            'Body'   => EntityBody::factory(fopen($file, 'r')),
            'ACL'    => CannedAcl::PUBLIC_READ
        ]);

        return $this->getObjectURL($directory, $name);
    }

    /**
     *
     * getObject
     *
     * Get the requested object
     *
     * @param   string  directory name
     * @param   string  name of the file
     * @return  object  the AWS object
     * @access  public
     *
     */
    public function getObject($directory, $name)
    {
        $directory = (string)$directory;
        $name      = (string)$name;

        if (self::$client->doesObjectExist(self::$bucket, $directory . '/' . $name)) {
            $object = self::$client->getObject([
                'Bucket' => self::$bucket,
                'Key'    => $directory . '/' . $name
            ]);

            return $object;
        } else {
            return null;
        }
    }

    /**
     *
     * getObjectURL
     *
     * Get the public url for the requested object
     *
     * @param   string  directory name
     * @param   string  name of the file
     * @return  string  public url or null if object does not exist
     * @access  public
     *
     */
    public function getObjectURL($directory, $name)
    {
        $directory = (string)$directory;
        $name      = (string)$name;

        if (self::$client->doesObjectExist(self::$bucket, $directory . '/' . $name)) {
            return "https://s3.amazonaws.com/" . self::bucket . '/' . $directory . '/' . $name;
        } else {
            return null;
        }
    }

    /**
     *
     * deleteObject
     *
     * Delete the given object or array of objects
     *
     * @param   string  the directory in which to delete
     * @param   mixed   string for 1 file, array for multiple files
     * @access  public
     *
     */
    public function deleteObject($directory, $name)
    {
        $directory = (string)$directory;

        if (is_string($name)) {
            self::$client->deleteObject([
               'Bucket' => self::$bucket,
               'Key'    => $directory. '/' . $name
            ]);
        } else {
            $files = [];

            foreach ($name as $item) {
                $files[] = [
                    'Key' => $directory . '/' . $item
                ];
            }

            self::$client->deleteObjects([
                'Bucket'  => self::$bucket,
                'Objects' => [$files]
            ]);
        }
    }
}
