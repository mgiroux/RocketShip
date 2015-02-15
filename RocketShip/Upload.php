<?php

namespace RocketShip;

use RocketShip\Helpers\Text;
use RocketShip\Database\Collection;

class Upload extends Base
{
    private static $files;
    private static $cache;

    public function __construct()
    {
        parent::__construct();

        $config = $this->app->config->uploading;
        $driver = ucfirst(strtolower($config->driver));

        include_once dirname(__FILE__) . '/Upload/' . $driver . '.php';
        $class        = '\\RocketShip\\Upload\\' . $driver;
        $this->driver = new $class;
    }

    /**
     *
     * Handle uploading of a file
     *
     * @param   array   the $_FILES array to upload ($_FILES[the_file_name])
     * @param   string  the type of file (files, images)
     * @param   string  the extensions to accept
     * @return  string  the file hash
     * @access  public
     *
     */
    public function handleUpload($file, $type="files", $extension='*')
    {
        $text = new Text;

        if ($file['error'] == 0) {
            $pos = strrpos($file['name'], '.');

            if ($extension != '*') {
                if (stristr($extension, ',')) {
                    $extensions = explode(",", $extension);
                } else {
                    $extensions = [$extension];
                }

                $pos = strrpos($file['name'], '.');
                $ext = strtolower(substr($file['name'], $pos + 1));

                if (!in_array($ext, $extensions)) {
                    return null;
                }
            }

            /* Unique name */
            $ext      = strtolower(substr($file['name'], $pos + 1));
            $filename = uniqid() . '.' . $ext;

            $file   = $this->driver->moveObject($file['tmp_name'], $type, $filename);
            $return = $this->writeFilesystem($type, $file);

            $this->app->events->trigger(Event::UPLOAD_DONE, $return);

            return $return;
        } else {
            $this->app->events->trigger(Event::UPLOAD_FAILED, $file);
            return null;
        }
    }

    /**
     *
     * Alias for handleUpload
     *
     */
    public function uploadFile($file, $extension='*')
    {
        return $this->handleUpload($file, 'files', $extension);
    }

    /**
     *
     * Alias for handleUpload
     *
     */
    public function uploadImage($file)
    {
        return $this->handleUpload($file, 'images', 'jpg,jpeg,png,gif');
    }

    /**
     *
     * Upload a local file to the CDN Cache system
     *
     * @param   string  the file to upload
     * @param   string  hash
     * @return  string  the new file's path
     * @access  public
     *
     */
    public function addCache($file, $hash)
    {
        $name   = basename($file);
        $file   = $this->driver->moveObject($file, 'cache', $name);
        $return = $this->writeCacheFilesystem($hash, $file);
        return $return;
    }

    /**
     *
     * Get a cached element url
     *
     * @param   string  element hash
     * @return  string  element url
     * @access  public
     *
     */
    public function getCache($hash)
    {
        $cache = $this->searchCacheFiles($hash);

        if (!empty($cache)) {
            return $cache->path;
        } else {
            return null;
        }
    }

    /**
     *
     * Check if given hash is already cached
     *
     * @param   string  the hash
     * @return  bool    true/false
     * @access  public
     *
     */
    public function isCached($hash)
    {
        $cache = $this->searchCacheFiles($hash);
        if (!empty($cache)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Delete one or many files
     *
     * @param   mixed   string for 1 file, array for many
     * @access  public
     *
     */
    public function deleteFile($hash)
    {
        if (is_array($hash)) {
            $names = [];

            foreach ($hash as $hash_string) {
                $names[] = $this->searchFileSystem($hash_string, 'name');
                $this->deleteFromFilesystem($hash_string);
                $this->app->events->trigger(Event::UPLOAD_DELETED, $hash_string);
            }

            $this->driver->deleteObject('files', $names);
        } else {
            $name = $this->searchFileSystem($hash, 'name');
            $this->driver->deleteObject('files', [$name]);
            $this->deleteFromFilesystem($hash);
            $this->app->events->trigger(Event::UPLOAD_DELETED, $hash);
        }
    }

    /**
     *
     * Delete one or many files
     *
     * @param   mixed   string for 1 file, array for many
     * @access  public
     *
     */
    public function deleteImage($hash)
    {
        if (is_array($hash)) {
            $names = [];

            foreach ($hash as $hash_string) {
                $names[] = $this->searchFileSystem($hash_string, 'name');

                $this->deleteFromFilesystem($hash_string);
                $this->deleteFromCache($hash_string);
                $this->app->events->trigger(Event::UPLOAD_DELETED, $hash_string);
            }

            $this->driver->deleteObject('images', $names);
        } else {
            $name = $this->searchFileSystem($hash, 'name');
            $this->driver->deleteObject('images', $name);
            $this->deleteFromFilesystem($hash);
            $this->app->events->trigger(Event::UPLOAD_DELETED, $hash);

            $cache_files = $this->searchCacheFiles($hash);
            foreach ($cache_files as $the_hash => $file) {
                $this->deleteFromCache($the_hash);
                $name = basename($file);

                $this->driver->deleteObject('cache', $name);
            }
        }
    }

    /**
     *
     * Get the requested file
     *
     * @param   string  the hash of the file
     * @param   string  what to return (path or name)
     * @return  string  the path to the file
     * @access  public
     *
     */
    public function get($hash, $type='path')
    {
        $file = $this->searchFileSystem($hash, $type);
        $this->app->events->trigger(Event::UPLOAD_GET, $file);
        return $file;
    }

    /**
     *
     * Get the raw object for the file
     *
     * @param   string  the hash of the file
     * @reutrn  mixed   depends on the driver
     * @access  public
     *
     */
    public function getRaw($hash)
    {
        $path = $this->get($hash);
        $name = $this->get($hash, 'name');

        $file = $this->driver->getObject($path, $name);
        $this->app->events->trigger(Event::UPLOAD_GET, $file);
        return $file;
    }

    /**
     *
     * Test the loaded driver for upload
     *
     * @param   string  full path to file
     * @param   string  name to use after upload
     * @return  bool    success / failure
     * @access  public
     *
     */
    public function testDriverUpload($file, $name)
    {
        $file = $this->driver->moveObject($file, 'files', $name);
        $return = $this->writeFilesystem('files', $file);
        echo "Created file with " . $return . ' as key';
    }

    /**
     *
     * Test the driver's Get URL capability
     *
     * @param   string  file name
     * @return  string  the file's url
     * @access  public
     *
     */
    public function testDriverGet($name)
    {
        return $this->driver->getObjectURL('files', $name);
    }

    /**
     *
     * Add a file to the filesystem
     *
     * @param   string  the type of file
     * @param   string  files public url
     * @return  string  the hash for the file (to be saved by application)
     * @access  private
     *
     */
    private function writeFileSystem($type, $filepath)
    {
        /* Fix for weird bug on specific php/apache setups */
        $hash   = 'f' . md5(uniqid());
        $name   = basename($filepath);
        $found  = $this->searchFileSystem($hash);

        if (empty($found)) {
            $model       = new Collection('uploaded_files');
            $model->hash = $hash;
            $model->path = (string)$filepath;
            $model->name = $name;
            $model->save();

            return $hash;
        } else {
            return $hash;
        }
    }

    /**
     *
     * Add a file to the cache filesystem
     *
     * @param   string  the hash
     * @param   string  files public url
     * @return  string  the hash for the file (to be saved by application)
     * @access  private
     *
     */
    private function writeCacheFileSystem($hash, $filepath)
    {
        $name   = basename($filepath);
        $found  = $this->getCache($hash);

        if (empty($found)) {
            $model       = new Collection('uploaded_caches');
            $model->hash = $hash;
            $model->path = (string)$filepath;
            $model->save();

            return $hash;
        } else {
            return $hash;
        }
    }

    /**
     *
     * Search the file system for the requested file
     *
     * @param   string  file hash
     * @param   string  the return value (path or name)
     * @return  string  the file path or null
     * @access  private
     *
     */
    private function searchFileSystem($hash, $return_value="path")
    {
        $model = new Collection('uploaded_files');

        $item = $model->where(['hash' => $hash])->find();

        if (!empty($item)) {
            if ($return_value == 'name') {
                return $item->name;
            }

            return $item->path;
        }
    }

    /**
     *
     * Delete a file from the file system
     *
     * @param   string  file hash
     * @access  private
     *
     */
    private function deleteFromFilesystem($hash)
    {
        $model = new Collection('uploaded_files');
        $model->where(['hash' => $hash])->destroy();
    }

    /**
     *
     * Delete a file from the cache file
     *
     * @param   string  file hash
     * @access  private
     *
     */
    private function deleteFromCache($hash)
    {
        $model = new Collection('uploaded_caches');
        $model->where(['hash' => $hash])->destroy();
    }

    /**
     *
     * Search for files that match the hash
     *
     * @param   string  hash
     * @return  array   cache hit
     * @access  private
     *
     */
    private function searchCacheFiles($hash)
    {
        $model = new Collection('uploaded_caches');
        $items = $model->where(['hash' => new \MongoRegex('/' . $hash . '/')])->find();
        return $items;
    }
}

/* Driver */
abstract class UploadDriver
{
    abstract public function moveObject($file, $directory, $name);
    abstract public function deleteObject($directory, $name);
    abstract public function getObject($directory, $name);
    abstract public function getObjectURL($directory, $name);
}
