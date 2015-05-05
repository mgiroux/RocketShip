<?php

namespace RocketShip\Upload;

use RocketShip\Configuration;
use RocketShip\UploadAdapter;

class Local implements UploadAdapter
{
    private $path;

    public function __construct()
    {
        $this->path = $_SERVER['DOCUMENT_ROOT'] . '/public/app/uploads/';
    }

    /**
     *
     * Upload a file to local storage
     *
     * @param   string  path name to file to upload
     * @param   string  directory to use (images, files)
     * @param   string  filename to use
     * @return  mixed   string: file path on success, null on error
     * @access  public
     *
     */
    public function moveObject($file, $directory, $name)
    {
        $final_file = $this->path . $directory . '/' . $name;

        if (!file_exists($this->path . $directory)) {
            mkdir($this->path . $directory);
        }

        copy($file, $final_file);
        return '/public/app/uploads/' . $directory . '/' . $name;
    }

    /**
     *
     * Get the requested object
     *
     * @param   string  directory name
     * @param   string  name of the file
     * @param   object  the file object
     * @return  string  the file url
     * @access  public
     *
     */
    public function getObject($directory, $name, $filedata)
    {
        return '/public/app/uploads/' . $directory . '/' . $name;
    }

    /**
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
        return '/public/app/uploads/' . $directory . '/' . $name;
    }

    /**
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
            unlink($this->path . $directory . '/' . $name);
        } else {
            foreach ($name as $item) {
                unlink($this->path . $directory . '/' . $item);
            }
        }
    }
}
