<?php

namespace RocketShip\Upload;

use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\Exception\S3Exception;
use Guzzle\Http\EntityBody;
use RocketShip\Configuration;

class Amazon extends \RocketShip\Utils\UploadDriver
{
    static private $client;
    static private $bucket;

    public function __construct()
    {
        if (empty(self::$client)) {
            $config           = Configuration::get('configuration', 'uploading');
            self::$bucket     = $config->authentication->bucket;
            self::$client     = S3Client::factory(array(
                'key'    => $config->authentication->key,
                'secret' => $config->authentication->secret
            ));
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
        $data = EntityBody::factory(fopen($file, 'r'));

        self::$client->putObject(array(
            'Bucket' => self::$bucket,
            'Key'    => $directory . '/' . $name,
            'Body'   => EntityBody::factory(fopen($file, 'r')),
            'ACL'    => CannedAcl::PUBLIC_READ
        ));

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
        if (self::$client->doesObjectExist(self::$bucket, $directory . '/' . $name)) {
            $object = self::$client->getObject(array(
                'Bucket' => self::$bucket,
                'Key'    => $directory . '/' . $name
            ));

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
        if (is_string($name)) {
            self::$client->deleteObject(array(
               'Bucket' => self::$bucket,
               'Key'    => $directory. '/' . $name
            ));
        } else {
            $files = [];

            foreach ($name as $item) {
                $files[] = array(
                    'Key' => $directory . '/' . $item
                );
            }

            self::$client->deleteObjects(array(
                'Bucket'  => self::$bucket,
                'Objects' => array($files)
            ));
        }
    }
}
