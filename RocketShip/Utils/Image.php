<?php

namespace RocketShip\Utils;

use PHPImageWorkshop\ImageWorkshop;
use RocketShip\Application;
use RocketShip\Upload;
use Number;
use String;

if (!extension_loaded('gd')) {
    throw new \RuntimeException("GD Extension is not available, cannot use Imaging library");
}

class Image
{
    private $layers;
    private $name;
    private $type_hint;

    /**
     *
     * __construct
     *
     * Initialize the image
     *
     * @param   int   string/string object path
     *
     */
    public function __construct($file)
    {
        $file = (string)$file;

        if (!empty($file)) {
            /* Image as first layer */
            if (stristr($file, 'http://') || stristr($file, 'https://')) {
                $this->layers = ImageWorkshop::initFromString(file_get_contents($file));
            } else {
                $this->layers = ImageWorkshop::initFromPath($file);
            }

            $app = Application::$instance;

            if ($app->config->uploading->driver == 'mongodb') {
                $type = explode('/', IO::getMimeType($file));

                if ($type == 'jpeg') { $type = 'jpg'; };
                $this->type_hint = $type[1];
            } else {
                $this->name      = str_replace(['.jpeg', '.jpg', '.gif', '.png'], '', strtolower(basename($file)));
                $this->type_hint = IO::getExtension($file);
            }
        }
    }

    /**
     *
     * resize
     *
     * Resize requested layer with width and height
     *
     * @param   int     width
     * @param   int     height
     * @param   int     layer id
     * @return  object  this object
     * @access  public
     *
     */
    public function resize($w, $h=0, $layer=0)
    {
        $w     = ($w instanceof Number) ? $w->raw() : $w;
        $h     = ($h instanceof Number) ? $h->raw() : $h;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $w = ($w == 0) ? null : $w;
        $h = ($h == 0) ? null : $h;

        if ($layer == 0) {
            $this->layers->resizeInPixel($w, $h, false);
            return $this;
        }

        $this->layers->layers[$layer]->resizeInPixel($w, $h, false);
        return $this;
    }

    /**
     *
     * resizeByWidth
     *
     * Resize requested layer by width (keep proportions)
     *
     * @param   int     width
     * @param   int     layer id
     * @return  object  this object
     * @access  public
     *
     */
    public function resizeByWidth($w, $layer=0)
    {
        $w     = ($w instanceof Number) ? $w->raw() : $w;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        if ($layer == 0) {
            $this->layers->resizeInPixel($w, null, true);
            return $this;
        }

        $this->layers->layers[$layer]->resizeInPixel($w, null, true);
        return $this;
    }

    /**
     *
     * resizeByHeight
     *
     * Resize requested layer by height (keep proportions)
     *
     * @param   int     height
     * @param   int     layer id
     * @return  object  this object
     * @access  public
     *
     */
    public function resizeByHeight($h, $layer=0)
    {
        $h     = ($h instanceof Number) ? $h->raw() : $h;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        if ($layer == 0) {
            $this->layers->resizeInPixel(null, intval((string)$h), true);
            return $this;
        }

        $this->layers->layers[intval((string)$layer)]->resizeInPixel(null, $h, true);
        return $this;
    }

    /**
     *
     * crop
     *
     * Crop an image starting at x/y
     *
     * @param   int     x
     * @param   int     y
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function crop($x=0, $y=0, $layer=0)
    {
        $x     = ($x instanceof Number) ? $x->raw() : $x;
        $y     = ($y instanceof Number) ? $y->raw() : $y;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $x = ($x == 0) ? null : $x;
        $y = ($y == 0) ? null : $y;

        if ($layer == 0) {
            $this->layers->cropMaximumInPercent($x, $y, 'LT');
            return $this;
        }

        $this->layers->layers[intval((string)$layer)]->cropMaximumInPercent($x, $y, 'LT');
        return $this;
    }

    /**
     *
     * cropFromCenter
     *
     * Crop an image from center
     *
     * @param   int     x
     * @param   int     y
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function cropFromCenter($x=0, $y=0, $layer=0)
    {
        $x     = ($x instanceof Number) ? $x->raw() : $x;
        $y     = ($y instanceof Number) ? $y->raw() : $y;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        if ($layer == 0) {
            $this->layers->cropMaximumInPercent($x, $y, 'MM');
            return $this;
        }

        $this->layers->layers[intval((string)$layer)]->cropMaximumInPercent($x, $y, 'MM');
        return $this;
    }

    /**
     *
     * thumbnail
     *
     * Generate a cropped thumbnail (square) of the source document
     *
     * @param   int     width
     * @param   int     height
     * @param   int     crop from x (0 = center)
     * @param   int     crop from y (0 = center)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @access  public
     *
     */
    public function thumbnail($w, $h, $x=0, $y=0, $layer=0)
    {
        $x     = ($x instanceof Number) ? $x->raw() : $x;
        $y     = ($y instanceof Number) ? $y->raw() : $y;
        $w     = ($w instanceof Number) ? $w->raw() : $w;
        $h     = ($h instanceof Number) ? $h->raw() : $h;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $x = ($x == 0) ? null : $x;
        $y = ($y == 0) ? null : $y;
        $w = ($w == 0) ? null : $w;
        $h = ($h == 0) ? null : $h;

        /* Thumbnail from center */
        if ($x == null && $y == null) {
            if ($h == null) {
                return $this->cropFromCenter($x, $y, $layer)->resizeByWidth($w, $layer);
            } elseif ($w == null) {
                return $this->cropFromCenter($x, $y, $layer)->resizeByHeight($h, $layer);
            } else {
                return $this->cropFromCenter($x, $y, $layer)->resize($w, $h, $layer);
            }
        }

        /* Thumbnail with crop position */
        if ($h == null) {
            return $this->crop($x, $y, $layer)->resizeByWidth($w, $layer);
        } elseif ($w == null) {
            return $this->crop($x, $y, $layer)->resizeByHeight($h, $layer);
        } else {
            return $this->crop($x, $y, $layer)->resize($w, $h, $layer);
        }
    }

    /**
     *
     * negative
     *
     * Reverse all colors of the layer
     *
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @access  public
     *
     */
    public function negative($layer=0)
    {
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_NEGATE);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_NEGATE);
        }

        return $this;
    }

    /**
     *
     * grayscale
     *
     * Generate a grayscale version of the layer
     *
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function grayscale($layer=0)
    {
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_GRAYSCALE);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_GRAYSCALE);
        }

        return $this;
    }

    /**
     *
     * brightness
     *
     * Change the brightness of the layer
     *
     * @param   int     brightness level (-255 to 255) (defaults to 100)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function brightness($level=100, $layer=0)
    {
        $level = ($level instanceof Number) ? $level->raw() : $level;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $level = ($level < -255) ? -255 : $level;
        $level = ($level > 255) ? 255 : $level;

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_BRIGHTNESS, $level);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_GRAYSCALE, $level);
        }

        return $this;
    }

    /**
     *
     * contrast
     *
     * Change contrast of the layer
     *
     * @param   int     contrast level (-100 to 100) (defaults to 50)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function contrast($level=50, $layer=0)
    {
        $level = ($level instanceof Number) ? $level->raw() : $level;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $level = ($level < -100) ? -100 : $level;
        $level = ($level > 100) ? 100 : $level;

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_CONTRAST, $level);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_CONTRAST, $level);
        }

        return $this;
    }

    /**
     *
     * blur
     *
     * Apply a gaussian blur to the layer
     *
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function blur($layer=0)
    {
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_GAUSSIAN_BLUR);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_GAUSSIAN_BLUR);
        }

        return $this;
    }

    /**
     *
     * pixelate
     *
     * Pixelate the layer
     *
     * @param   int     pixel size (defaults to 3)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function pixelate($level=3, $layer=0)
    {
        $level = ($level instanceof Number) ? $level->raw() : $level;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_PIXELATE, $level);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_PIXELATE, $level);
        }

        return $this;
    }

    /**
     *
     * smooth
     *
     * Try to smooth the pixels in the layer
     *
     * @param   int     smoothness level (-8 to 8) (defaults to 3)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function smooth($level=3, $layer=0)
    {
        $level = ($level instanceof Number) ? $level->raw() : $level;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $level = ($level < -8) ? -8 : $level;
        $level = ($level > 8) ? 8 : $level;

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_SMOOTH, $level);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_SMOOTH, $level);
        }

        return $this;
    }

    /**
     *
     * colorize
     *
     * Colorize the layer (r/g/b/a)
     *
     * @param   int     red value  (-255 to 255)
     * @param   int     green value (-255 to 255)
     * @param   int     blue value (-255 to 255)
     * @param   int     alpha (0 = opaque, 127 = transparent)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function colorize($red, $green, $blue, $alpha=0, $layer=0)
    {
        $red = ($red instanceof Number) ? $red->raw() : $red;
        $green = ($green instanceof Number) ? $green->raw() : $green;
        $blue = ($blue instanceof Number) ? $blue->raw() : $blue;
        $alpha = ($alpha instanceof Number) ? $alpha->raw() : $alpha;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $red   = intval((string)$red);
        $green = intval((string)$green);
        $blue  = intval((string)$blue);
        $alpha = intval((string)$alpha);
        $layer = intval((string)$layer);

        $red   = ($red < -255) ? -255 : $red;
        $green = ($red < -255) ? -255 : $green;
        $blue  = ($red < -255) ? -255 : $blue;
        $red   = ($red > 255) ? 255 : $red;
        $green = ($red > 255) ? 255 : $green;
        $blue  = ($red > 255) ? 255 : $blue;

        $alpha = ($alpha < 0) ? 0 : $alpha;
        $alpha = ($alpha > 127) ? 127 : $alpha;

        if ($layer == 0) {
            $this->layers->applyFilter(IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
        } else {
            $this->layers[$layer]->applyFilter(IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
        }

        return $this;
    }

    /**
     *
     * flip
     *
     * Flip the layer vertically or horizontally
     *
     * @param   mixed   string or integer (horizontal, horizon, 0) or (vertical, vert, 1)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function flip($direction='horizontal', $layer=0)
    {
        $direction = (string)$direction;
        $layer     = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        switch ($direction)
        {
            case 'horizontal':
            case 'horizon':
            case 0:
                if ($layer == 0) {
                    $this->layers->flip('horizontal');
                } else {
                    $this->layers[$layer]->flip('horizontal');
                }
                break;

            case 'vertical':
            case 'vert':
            case 1:
            if ($layer == 0) {
                $this->layers->flip('vertical');
            } else {
                $this->layers[$layer]->flip('vertical');
            }
                break;
        }

        return $this;
    }

    /**
     *
     * opacity
     *
     * Set opacity for layer (lower = more transparent)
     *
     * @param   int     level (defaults to 50)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function opacity($level=50, $layer=0)
    {
        $level = ($level instanceof Number) ? $level->raw() : $level;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $level = ($level < 0) ? 0 : $level;
        $level = ($level > 100) ? 100 : $level;

        if ($layer == 0) {
            $this->layers->opacity($level);
        } else {
            $this->layers[$layer]->opacity($level);
        }

        return $this;
    }

    /**
     *
     * rotate
     *
     * @param   int     degress (-360 to 360) (defaults to -45)
     * @param   int     layer to affect (0 = background)
     * @return  object  this object
     * @public
     *
     */
    public function rotate($degree=-45, $layer=0)
    {
        $degree = ($degree instanceof Number) ? $degree->raw() : $degree;
        $layer = ($layer instanceof Number) ? $layer->raw() : $layer;

        if (empty($this->layers)) { return $this; }

        $degree = ($degree < -360) ? -360 : $degree;
        $degree = ($degree > 360) ? 360 : $degree;

        if ($layer == 0) {
            $this->layers->rotate($degree);
        } else {
            $this->layers[$layer]->rotate($degree);
        }

        return $this;
    }

    /**
     *
     * addLayer
     *
     * Add a layer on top of the others
     *
     * @param   string  the file to create it from
     * @param   int     x (position of the layer) (defaults to 0)
     * @param   int     y (position of the layer) (default to 0)
     * @param   string  anchor point (LT, LB, RT, RB) (Defaults to LT)
     * @return  object  this object
     * @access  public
     *
     */
    public function addLayer($file, $x=0, $y=0, $position='LT')
    {
        $file     = (string)$file;
        $x        = ($x instanceof Number) ? $x->raw() : $x;
        $y        = ($y instanceof Number) ? $y->raw() : $y;
        $position = (string)$position;

        if (empty($this->layers)) { return $this; }

        if (stristr($file, 'http://') || stristr($file, 'https://')) {
            $layer = ImageWorkshop::initFromString(file_get_contents($file));
        } else {
            $layer = ImageWorkshop::initFromPath($file);
        }

        $this->layers->addLayerOnTop($layer, $x, $y, $position);

        return $this;
    }

    /**
     *
     * outputFile
     *
     * Output the image to file by using the application's upload adapter
     *
     * @param   string  the target name
     * @param   int     quality from 0 to 100
     * @param   string  type of file to output (gif, png, jpg) (defaults to : autodetect)
     * @param   string  background color (defaults to null, required for opacity transformations)
     * @param   bool    return the hash only or full path
     * @return  string  the full path of the file or the hash
     * @access  public
     *
     */
    public function outputFile($target, $quality=80, $type=null, $color=null, $output_hash=false)
    {
        $target  = (string)$target;
        $quality = ($quality instanceof Number) ? $quality->raw() : $quality;
        $type    = (string)$type;
        $color   = (string)$color;

        if (empty($this->layers)) { return ''; }

        if (empty($type)) {
            $type = $this->type_hint;
        }

        switch ($type)
        {
            case 'gif':
                $quality = null;
                break;

            case 'png':
                $quality = ($quality > 90) ? 9 : ceil($quality / 10);
                break;
        }

        /* Save to temporary folder */
        $path   = dirname(dirname(__DIR__)) . '/app/tmp/';
        $target = str_replace(['.jpeg', '.jpg', 'gif', '.png'], '', $target) . '.' . $type;
        $this->layers->save($path, $target, false, $color, $quality);

        /* Ask upload to move it */
        $file = [
            'tmp_name' => $path . $target,
            'name'     => $target,
            'error'    => 0
        ];

        $upload = new Upload;
        $hash   = $upload->uploadImage($file, $target);

        /* Destroy temporary file */
        unlink($path . $target);

        if ($output_hash) {
            return String::init($hash);
        } else {
            return String::init($upload->get($hash));
        }
    }

    /**
     *
     * outputTemp
     *
     * Output the image temporarily to the system temp folder
     *
     * @param   string  the target name
     * @param   int     quality from 0 to 100
     * @param   string  type of file to output (gif, png, jpg) (defaults to : autodetect)
     * @param   string  background color (defaults to null, required for opacity transformations)
     * @return  string  the full path to file
     * @access  public
     *
     */
    public function outputTemp($target, $quality=80, $type=null, $color=null, $output_hash=false)
    {
        $target  = (string)$target;
        $quality = ($quality instanceof Number) ? $quality->raw() : $quality;
        $type    = (string)$type;
        $color   = (string)$color;

        if (empty($this->layers)) { return ''; }

        if (empty($type)) {
            $type = $this->type_hint;
        }

        switch ($type)
        {
            case 'gif':
                $quality = null;
                break;

            case 'png':
                $quality = ($quality > 90) ? 9 : ceil($quality / 10);
                break;
        }

        /* Save to temporary folder */
        $path   = dirname(dirname(__DIR__)) . '/app/tmp/';
        $target = str_replace(['.jpeg', '.jpg', 'gif', '.png'], '', $target) . '.' . $type;
        $this->layers->save($path, $target, false, $color, $quality);

        return String::init($path . $target);
    }

    /**
     *
     * output
     *
     * Output the image data to the browser directly
     *
     * @param   int     quality from 0 to 100
     * @param   string  type of file to output (gif, png, jpg) (defaults to : autodetect)
     * @param   string  background color (defaults to null, required for opacity transformations)
     * @access  public
     *
     */
    public function output($quality=80, $type=null, $color=null)
    {
        $quality = ($quality instanceof Number) ? $quality->raw() : $quality;
        $type    = (string)$type;
        $color   = (string)$color;

        if (empty($this->layers)) { return ''; }

        $this->layers->mergeAll();
        $image = $this->layers->getResult($color);

        if (empty($type)) {
            $type = $this->type_hint;
        }

        switch ($type)
        {
            case 'gif':
                header('Content-type: image/gif');
                header('Content-Disposition: filename="' . $this->name . '.gif"');
                imagegif($image);
                break;

            case 'png':
                header('Content-type: image/png');
                header('Content-Disposition: filename="' . $this->name . '.png"');
                $quality = ($quality > 90) ? 9 : ceil($quality / 10);
                imagepng($image, null, $quality);
                break;

            case 'jpeg':
            case 'jpg':
            default:
                header('Content-type: image/jpeg');
                header('Content-Disposition: filename="' . $this->name . '.jpg"');
                imagejpeg($image, null, $quality);
                break;
        }

        exit;
    }

    /**
     *
     * tag
     *
     * Generate an HTML img tag for the given image hash, this method keeps proportions
     *
     * @param   string  file hash
     * @param   int     width to use
     * @param   int     height (optional)
     * @param   array   list of options (class, title, alt, id) (optional)
     * @return  string  HTML tag code
     * @access  public
     * @static
     *
     */
    public static function tag($file, $width, $height=null, $options=[])
    {
        $file    = (string)$file;
        $width   = ($width instanceof Number) ? $width->raw() : $width;
        $height  = ($height instanceof Number) ? $height->raw() : $height;
        $options = ($options instanceof Collection) ? $options->raw() : $options;

        /* Hash given */
        $upload = new Upload;
        $cached = $upload->isCached($width . 'x' . $height . '_' . $file);

        $options = self::_options($options);

        if (!$cached) {
            $img = new self($upload->get($file));

            if (empty($height)) {
                $img->resizeByWidth($width);
            } elseif (empty($width)) {
                $img->resizeByHeight($height);
            } else {
                $img->resize($width, $height);
            }

            $src  = $img->outputTemp($width . 'x' . $height . '_' . $file);
            $file = $upload->getCache($upload->addCache($src, $width . 'x' . $height . '_' . $file));
            unlink($src);
        } else {
            $file = $upload->getCache($width . 'x' . $height . '_' . $file);
        }

        if (empty($height)) {
            $measure = 'width="' . $width . '"';
        } elseif (empty($width)) {
            $measure = 'height="' . $height . '"';
        } else {
            $measure = 'width="' . $width . '" height="' . $height . '"';
        }

        echo String::init('<img src="' . $file . '" ' . $measure . ' ' . $options . '/>');
    }

    /**
     *
     * get
     *
     * Generate or get the cache for requested image
     *
     * @param   string  file hash
     * @param   int     width to use
     * @param   int     height (optional)
     * @param   bool    crop from center?
     * @return  string  HTML tag code
     * @access  public
     * @static
     *
     */
    public static function get($file, $width, $height=null, $fromCenter=false)
    {
        $file    = (string)$file;
        $width   = ($width instanceof Number) ? $width->raw() : $width;
        $height  = ($height instanceof Number) ? $height->raw() : $height;

        /* Hash given */
        $upload = new Upload;
        $cached = $upload->isCached($width . 'x' . $height . '_' . $file);

        $app = Application::$instance;

        if (!$cached) {
            if ($app->config->uploading->driver == 'mongodb') {
                $img = new self($app->site_url . '/public/uploads/files/' . $file);
            } else {
                $img = new self($upload->get($file));
            }

            if (!$fromCenter) {
                if (empty($height)) {
                    $img->resizeByWidth($width);
                } elseif (empty($width)) {
                    $img->resizeByHeight($height);
                } else {
                    $img->resize($width, $height);
                }
            } else {
                $img->thumbnail($width, $height);
            }

            $src      = $img->outputTemp($width . 'x' . $height . '_' . $file);
            $filedata = $upload->getCache($upload->addCache($src, $width . 'x' . $height . '_' . $file));
            unlink($src);
        } else {
            $filedata = $upload->getCache($width . 'x' . $height . '_' . $file);
        }

        if ($app->config->uploading->driver == 'mongodb') {
            $file = $app->site_url . '/public/uploads/files/' . $filedata;
        } else {
            $file = $app->site_url . '/public/uploads/' . $filedata;
        }

        return String::init($file);
    }

    /**
     *
     * Get an image's size without downloading it
     *
     * @param   string  url of the image
     * @return  object  width and height
     * @access  public
     * @static
     *
     */
    public static function getImageSize($url)
    {
        $url = (string)$url;

        $headers = ["Range: bytes=0-32768"];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);

        $im     = imagecreatefromstring($data);
        $width  = imagesx($im);
        $height = imagesy($im);

        $obj         = new \stdClass;
        $obj->width  = $width;
        $obj->height = $height;

        return $obj;
    }

    /**
     *
     * _options
     *
     * Build the arguments for the tag method
     *
     * @param   array   list of arguments
     * @return  string  the arguments
     * @private
     * @static
     *
     */
    private static function _options($options)
    {
        $options = ($options instanceof Collection) ? $options->raw() : $options;

        /* Class */
        if (isset($options['class'])) {
            $class = ' class="' . $options['class'] . '"';
        } else {
            $class = '';
        }

        /* Id */
        if (isset($options['id'])) {
            $id = ' id="' . $options['id'] . '"';
        } else {
            $id = '';
        }

        if (isset($options['alt'])) {
            $alt = $options['alt'];
        } else {
            $alt = 'Image';
        }

        if (isset($options['title'])) {
            $title = ' title="' . $options['title'] . '"';
        } else {
            $title = '';
        }

        return String::init('alt="' . $alt . '" ' . $class . $id . $title);
    }
}