<?php

namespace RocketShip\Upload;

use RocketShip\Application;
use RocketShip\Configuration;
use RocketShip\Database\Collection;
use RocketShip\UploadAdapter;

class Mongodb implements UploadAdapter
{
    private $path;
    private $model;

    public function __construct()
    {
        $this->model = new Collection('uploads', true);
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
        $mime = $this->getMime($file);
        return $this->model->addFile($file, true, false, $mime);
    }

    /**
     *
     * Get the requested object
     *
     * @param   string  directory name (in this case the file id)
     * @param   string  name of the file
     * @return  string  the file url
     * @access  public
     *
     */
    public function getObject($directory, $name)
    {
        return $this->model->getFileById($directory);
    }

    /**
     *
     * Get the public url for the requested object
     *
     * @param   string  directory name (in this case, the file hash)
     * @param   string  name of the file
     * @return  string  public url or null if object does not exist
     * @access  public
     *
     */
    public function getObjectURL($directory, $name)
    {
        $app = Application::$instance;
        return $app->site_url . '/public/uploads/files/' . $directory;
    }

    /**
     *
     * Delete the given object or array of objects
     *
     * @param   string  the directory in which to delete (in this case, the file id)
     * @param   mixed   string for 1 file, array for multiple files
     * @access  public
     *
     */
    public function deleteObject($directory, $name)
    {
        $this->model->destroyFileById($directory);
    }

    /**
     *
     * Get the mime type for the give file
     *
     * @param   string  the absolute file path
     * @return  string  mime type
     * @access  private
     *
     */
    private function getMime($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file);
        finfo_close($finfo);

        foreach ($_FILES as $key => $filedata) {
            if ($filedata['tmp_name'] = $file) {
                /* Make sure we find office files correctly */
                $original = $filedata['name'];

                switch(strtolower(preg_replace('/^.*\./','', $original)))
                {
                    case 'docx':
                        return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                    case 'docm':
                        return 'application/vnd.ms-word.document.macroEnabled.12';
                    case 'dotx':
                        return 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
                    case 'dotm':
                        return 'application/vnd.ms-word.template.macroEnabled.12';
                    case 'xlsx':
                        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    case 'xlsm':
                        return 'application/vnd.ms-excel.sheet.macroEnabled.12';
                    case 'xltx':
                        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
                    case 'xltm':
                        return 'application/vnd.ms-excel.template.macroEnabled.12';
                    case 'xlsb':
                        return 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
                    case 'xlam':
                        return 'application/vnd.ms-excel.addin.macroEnabled.12';
                    case 'pptx':
                        return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                    case 'pptm':
                        return 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
                    case 'ppsx':
                        return 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
                    case 'ppsm':
                        return 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
                    case 'potx':
                        return 'application/vnd.openxmlformats-officedocument.presentationml.template';
                    case 'potm':
                        return 'application/vnd.ms-powerpoint.template.macroEnabled.12';
                    case 'ppam':
                        return 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
                    case 'sldx':
                        return 'application/vnd.openxmlformats-officedocument.presentationml.slide';
                    case 'sldm':
                        return 'application/vnd.ms-powerpoint.slide.macroEnabled.12';
                    case 'one':
                        return 'application/msonenote';
                    case 'onetoc2':
                        return 'application/msonenote';
                    case 'onetmp':
                        return 'application/msonenote';
                    case 'onepkg':
                        return 'application/msonenote';
                    case 'thmx':
                        return 'application/vnd.ms-officetheme';
                }

                break;
            }
        }

        return $mime;
    }
}
