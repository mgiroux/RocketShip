<?php

namespace RocketShip\Upload;

use RocketShip\Application;
use RocketShip\Configuration;
use RocketShip\UploadAdapter;

use Cloudinary as Cloud;
use Cloudinary\Uploader;
use Cloudinary\Api;

class Cloudinary implements UploadAdapter
{
    private $app;

    public function __construct()
    {
        $this->app = Application::$instance;
        $config    = $this->app->config->uploading;

        Cloud::config([
            "cloud_name" => $config->authentication->bucket,
            "api_key"    => $config->authentication->key,
            "api_secret" => $config->authentication->secret
        ]);
    }

    /**
     *
     * moveObject
     *
     * Upload a file to Cloudinary Cloud servers
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
        $data = Uploader::upload($file);

        $out = new \stdClass;

        $out->filepath  = $data['url'];
        $out->public_id = $data['public_id'];
        $out->width     = $data['width'];
        $out->height    = $data['height'];
        $out->format    = $data['format'];

        return $out;
    }

    /**
     *
     * getObject
     *
     * Get the requested object
     *
     * @param   string  directory name
     * @param   string  name of the file
     * @param   object  the file object
     * @return  object  the Cloudinary object
     * @access  public
     *
     */
    public function getObject($directory, $name, $filedata)
    {
        $api  = new Api;
        $data = $api->resource($filedata->meta['public_id']);

        if (!empty($data)) {
            $out = $data->getArrayCopy();
            return $out['url'];
        }

        return null;
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
        $api  = new Api;
        $data = $api->resource($directory . '_' . $name);

        if (!empty($data)) {
            return $data->storage['url'];
        }

        return null;
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
        $api = new Api;

        $api->delete_resources([$directory . '_' . $name]);
    }
}