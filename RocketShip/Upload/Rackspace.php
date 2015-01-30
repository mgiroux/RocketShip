<?php

namespace RocketShip\Upload;

use OpenCloud\Base\Exceptions\HttpError;
use OpenCloud\Common\Exceptions\IOError;
use RocketShip\Configuration;
use RocketShip\Utils\IO;

class Rackspace extends \RocketShip\Utils\UploadDriver
{
    static private $connection;
    static private $object_store;
    static private $container;

    /**
     *
     * construct
     *
     * Create an instance and connect to rackspace
     *
     * @access  public
     *
     */
    public function __construct()
    {
        if (empty(self::$connection)) {
            $config = Configuration::get('configuration', 'uploading');

            self::$connection = new \OpenCloud\Rackspace(
                'https://identity.api.rackspacecloud.com/v2.0',
                array(
                    'username'   => $config->authentication->user,
                    'apiKey'     => $config->authentication->key,
                    'tenantName' => $config->authentication->secret
                )
            );

            self::$object_store = self::$connection->ObjectStore('cloudFiles', 'DFW', 'publicURL');
            self::$container    = self::$object_store->Container($config->authentication->bucket);
        }
    }

    /**
     *
     * moveObject
     *
     * Upload a file to Rackspace Cloud servers
     *
     * @param   string  path name to file to upload
     * @param   string  directory to use (images, files)
     * @param   string  filename to use on rackspace
     * @return  mixed   string: file path on success, null on error
     * @access  public
     *
     */
    public function moveObject($file, $directory, $name)
    {
        $fileobj = self::$container->DataObject();
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $type    = finfo_file($finfo, $file);
        finfo_close($finfo);

        $fileobj->Create(array('name' => $directory . '/' . $name, 'content_type' => $type), $file);
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
     * @return  object  the rackspace object
     * @access  public
     *
     */
    public function getObject($directory, $name)
    {
        $item = new \stdClass;

        $fileobj           = self::$container->DataObject($directory . '/' . $name);
        $item->name        = $fileobj ->name;
        $item->size        = new \stdClass;
        $item->size->bytes = $fileobj ->bytes;
        $item->size->clean = IO::getSize($fileobj->bytes);
        $item->lastmod     = strtotime($fileobj ->last_modified);
        $item->url         = new \stdClass;
        $item->url->http   = $fileobj ->publicURL();
        $item->url->https  = $fileobj ->publicURL('SSL');

        return $item;
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
        $fileobj = self::$container->DataObject($directory . '/' . $name);
        return $fileobj->publicURL('SSL');
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
            echo $directory . '/' . $name;
            $obj = self::$container->DataObject($directory . '/' . $name);
            $obj->Delete();
        } else {
            foreach ($name as $item) {
                $obj = self::$container->DataObject($directory . '/' . $item);
                $obj->Delete();
            }
        }
    }
}
