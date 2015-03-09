<?php

namespace RocketShip\Utils;

use RocketShip\Base;
use String;
use Collection;

class IO extends Base
{
    public $size;
    public $path;
    public $file;
    public $permissions;
    public $mtime;
    public $atime;
    public $type;
    public $extension;
    public $content;
    public $error;

    /**
     *
     * Get every information about the file
     *
     * @param   string    full file (with path)
     * @param   bool      open the file and store it in $content ?
     * @access  public
     *
     */
    public function __construct($file, $open=false)
    {
        parent::__construct();

        if (file_exists($file)) {
            $fp = fopen($file, 'r');
            $stats = fstat($fp);
            fclose($fp);

            /* Basics */
            $this->file = $file;
            $this->size = $this->getSize($stats['size']);

            /* Access and last modification time */
            $modified = \DateTime::createFromFormat('U', $stats['atime']);
            $this->mtime = $modified->format('Y-m-d H:i:s');

            $accessed = \DateTime::createFromFormat('U', $stats['atime']);
            $this->atime = $accessed->format('Y-m-d H:i:s');

            /* Path, file type */
            $this->path = dirname($file);
            $this->type = filetype($file);

            /* file extension */
            if ($this->type == 'file') {
                $infos = pathinfo($file);
                $this->extension = $infos['extension'];
            }

            /* Permissions and status (writeable, readable) */
            $this->permissions = fileperms($file);
            $this->readable    = is_readable($file);
            $this->writable    = is_writable($file);

            $this->error = null;

            if ($open) {
                $this->content = String::init(file_get_contents($file));
            }
        } else {
            $this->error = String::init('file not found');
        }
    }

    /**
     *
     * Convert a bytes value into bytes, mb, gb, tb value
     *
     * @param   int       size to calculate
     * @return  string    file size
     * @access  public
     * @static
     *
     */
    public static function getSize($size)
    {
        if ($size < 1024) {
            return String::init(round($size, 2) . ' Byte');
        } elseif ($size < (1024 * 1024)) {
            return String::init(round(($size / 1024), 2) . ' KB');
        } elseif ($size < (1024 * 1024 * 1024)) {
            return String::init(round((($size / 1024) / 1024), 2) . ' MB');
        } elseif ($size < (1024 * 1024 * 1024 * 1024)) {
            return String::init(round(((($size / 1024) / 1024) / 1024), 2) . ' GB');
        } else {
            return $size;
        }
    }

    /**
     *
     * Get the formatted size of a remote file without downloading that file
     *
     * @param   string  FQDN of the file
     * @return  string  formatted filesize
     * @access  public
     * @static
     *
     */
    public static function getRemoteSize($file)
    {
        $headers = get_headers($file, 1);
        $size    = $headers['Content-Length'];
        return IO::getSize($size);
    }

    /**
     *
     * Comment
     *
     * @param   string    description
     * @return  void
     * @access  public
     *
     */
    public function write($data)
    {
        if (!$this->error) {
            file_put_contents($this->file, (string)$data);
        }
    }

    /**
     *
     * Append data to a file
     *
     * @param    string    data to append
     * @return   void
     * @access   public
     *
     */
    public function append($data)
    {
        if (!$this->error) {
            $pre = file_get_contents($this->file);
            $data = $pre . (string)$data;
            $this->write($data);
        }
    }

    /**
     *
     * Prepend data to a file
     *
     * @param    string    data to prepend
     * @return   void
     * @access   public
     *
     */
    public function prepend($data)
    {
        if (!$this->error) {
            $app = file_get_contents($this->file);
            $data = (string)$data . $app;
            $this->write($data);
        }
    }

    /**
     *
     * Rename the current file to new name
     *
     * @param    string    new filename
     * @return   void
     * @access   public
     *
     */
    public function rename($name)
    {
        if ($this->writable) {
            rename($this->file, $this->path . '/' . $name);
        }
    }

    /**
     *
     * delete the file
     *
     * @return   void
     * @access   public
     *
     */
    public function delete()
    {
        if ($this->writable) {
            unlink($this->file);
        }
    }

    /**
     *
     * Create a file or directory with the given file (from construct)
     *
     * @param    bool      the file is a directory?
     * @return   void
     * @access   public
     *
     */
    public function create($is_dir=false)
    {
        if (!file_exists($this->file)) {
            if (!$is_dir) {
                if (touch($this->file)) {
                    $this->error = null;
                }
            } else {
                if (mkdir($this->file)) {
                    $this->error = null;
                }
            }
        }
    }

    /**
     *
     * Set permissions on the current file
     *
     * @param    int       permissions to set (defaults to 755)
     * @return   void
     * @access   public
     *
     */
    public function chmod($perm=755)
    {
        if ($this->writable) {
            if (strlen($perm) < 4) {
                $perm = '0' + $perm;
            }

            chmod($this->file, $perm);
        }
    }

    /**
     *
     * Get the files within the directory (if file is a directory)
     *
     * @param    bool      recursively check
     * @param    string    file to look in
     * @return   Collection
     * @access   public
     *
     */
    public function getFiles($recursive=true, $file=null)
    {
        if ($this->type == 'dir') {
            $list = [];

            if (empty($file)) {
                $files = glob($this->file . '/*');
            } else {
                $files = glob($file . '/*');
            }

            if ($recursive) {
                foreach ($files as $num => $file) {
                    if (is_dir($file)) {
                        $list[] = $this->getFiles(true, $file);
                    } else {
                        $list[] = $file;
                    }
                }
            } else {
                $list = $files;
            }

            return Collection::init($list);
        } else {
            return Collection::init([]);
        }
    }

    /**
     *
     * Check if given directory is writable
     *
     * @param    string    path to look at
     * @param    bool      warn if the directory is not writable
     * @param    bool      whether the root path is already handed
     * @param    bool      die on warning or not
     * @return   bool      true/false
     * @access   public
     * @static
     *
     */
    public static function isDirectoryWritable($dir, $warn=false, $add_root=true, $die=false)
    {
        $dir = (string)$dir;

        if ($add_root) {
            $root = dirname(dirname(__DIR__)) . '/' . $dir;
        } else {
            $root = $dir;
        }

        $length  = strlen(dirname(dirname(__DIR__)));
        $dirname = substr($root, $length);

        if (!is_writable($root)) {
            if ($warn) {
                throw new \RuntimeException($dir . " is not writable, please make sure it is chmod+774 (or 777 if apache user is not the same as FTP user)!");
            }

            return false;
        } else {
            $files = glob($root . '/*');
            foreach ($files as $num => $file) {
                if (is_dir($file)) {
                    $return = self::isDirectoryWritable($file, $warn, false);

                    if (!$return) {
                        return false;
                    }
                }
            }

            return true;
        }
    }

    /**
     *
     * Check if given file is writable
     *
     * @param    string    file to look at
     * @param    bool      warn if the file is not writable
     * @param    bool      die on warning or not
     * @return   bool      true/false
     * @access   public
     * @static
     *
     */
    public static function isWritable($file, $warn=false, $die=false)
    {
        $file = (string)$file;

        if (!is_writable($file)) {
            if ($warn) {
                throw new \RuntimeException($file . " is not writable, please make sure it is chmod+774 (or 777 if apache user is not the same as FTP user)!");
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * Get the extension of given file
     *
     * @param    string    file to get the extension of
     * @return   string    extension
     * @access   public
     *
     */
    public static function getExtension($filename)
    {
        $filename = (string)$filename;

        $name = basename($filename);
        $pos = strrpos($name, '.');
        $ext = substr($name, $pos+1);
        return String::init($ext);
    }

    /**
     *
     * Get the mime-type for the given file (supports remote files)
     *
     * Note: Requires finfo PECL extension for local files
     *
     * @param   string  file url
     * @return  string  the mime type
     * @access  public
     * @static
     *
     */
    public static function getMimeType($file)
    {
        $file = (string)$file;

        if (stristr($file, 'http')) {
            $ch = curl_init($file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_exec($ch);
            return String::init(curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $info  = finfo_file($finfo, $file);
            finfo_close($finfo);
            return String::init($info);
        }
    }
}