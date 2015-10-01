<?php

namespace RocketShip;

use RocketShip\Helpers\HTML;
use RocketShip\Directives;

class View extends Base
{
    /**
     *
     * JSON format
     *
     * @var String
     *
     */
    const JSON = AssetTypes::JSON;

    /**
     *
     * HTML format
     *
     * @var String
     *
     */
    const HTML = AssetTypes::HTML;

    /**
     *
     * Data container
     *
     * @var \stdClass
     *
     */
    protected $data;

    /**
     *
     * HTML helper
     *
     * @var HTML
     *
     */
    protected $html;

    /**
     *
     * Set the view to rendered
     *
     * @var Bool
     *
     */
    public $rendered = false;

    /**
     *
     * The layout to use by default
     *
     * @var String
     *
     */
    private $layout = 'default';

    /**
     *
     * View path
     *
     * @var String
     *
     */
    private $path;

    /**
     *
     * Path to the assets
     *
     * @var String
     *
     */
    private $assets_path;

    /**
     *
     * Construct
     *
     * @access  public
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->layout = 'default';
        $this->data   = new \stdClass;
        $this->html   = new HTML;
    }

    /**
     *
     * Set the view path
     *
     * @param   string  the path
     * @access  public
     *
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     *
     * Set the assets path
     *
     * @param   string  the path
     * @access  public
     *
     */
    public function setAssetsPath($path)
    {
        $this->assets_path = $path;
    }

    /**
     *
     * Set a value for the given key to be available at view level
     *
     * @param   string  the key for the value
     * @param   mixed   the value
     * @access  public
     *
     */
    public function set($key, $value)
    {
        $this->data->{$key} = $value;
    }

    /**
     *
     * Batch set values
     *
     * @param   array   array of keys
     * @param   array   array of values
     * @access  public
     *
     */
    public function batch($keys, $values)
    {
        foreach ($keys as $num => $key) {
            $this->set($key, $values[$num]);
        }
    }

    /**
     *
     * Get a value for the given key
     *
     * @param   string  the key
     * @return  mixed   the value if it exists
     * @access  public
     *
     */
    public function __get($key)
    {
        if ($key != 'rendered' && $key != 'path') {
            if (!empty($this->data->{$key})) {
                return $this->data->{$key};
            }
        } else {
            if ($key == 'rendered') {
                return $this->rendered;
            } else {
                return $this->path;
            }
        }

        return null;
    }

    /**
     *
     * Get the data object
     *
     * @return  object  the view's data object
     * @access  public
     *
     */
    public function get()
    {
        return $this->data;
    }

    /**
     *
     * Use a different layout than the default
     *
     * @param   string  the layout file without .html
     * @access  public
     *
     */
    public function useLayout($layout)
    {
        if (stristr($layout, '@')) {
            /* Bundle layout reference */
            $conf                  = explode('@', $layout);
            $path                  = $this->app->root_path . '/bundles/' . ucfirst(strtolower($conf[1])) . '/views/layouts/' . $conf[0];
            $this->layout          = 'ref:' . $path;
            $this->assets_path     = $this->app->site_url . '/public/' . $conf[1];
        } else {
            $this->layout = $layout;
        }
    }

    /**
     *
     * render
     *
     * Render a template (automatically load layout)
     *
     * @param     string    the template (without ,html)
     * @param     string    the format to output as (json or html)
     * @throws    \Exception
     * @return    void
     * @access    public
     * @final
     */
    public final function render($template, $format=self::HTML)
    {
        if (Routing::$json_flag == true) {
            $format = self::JSON;
        }

        /* Render json only */
        if ($format == self::JSON) {
            header('Content-type: application/json');
            echo json_encode($this->data);
            $this->rendered = true;
            return;
        }

        $addition = null;

        if ($this->app->request->isMobile() && $this->app->config->general->is_reponsive == 'no') {
            $addition = '_mobile';
        }

        $lang = $this->app->session->get('app_language');
        if (!empty($lang)) {
            $this->language = $this->app->session->get('app_language');
        } else {
            $this->language = $this->app->config->languages->default;
        }

        $html = null;

        /* No double renders */
        if ($this->rendered == true) {
            return;
        }

        $ofile = $this->path . '/' . strtolower($template) . $addition . '.html';

        if (file_exists($ofile)) {
            $file = $ofile;
        } else {
            $file = $this->path . '/' . strtolower($template) . $addition . '.html';
        }

        /* Support bundle layout reference */
        if (stristr($this->layout, 'ref:')) {
            $layout = substr($this->layout, 4) . '.html';
        } else {
            $layout = dirname($this->path) . '/layouts/' . $this->layout . $addition . '.html';
        }

        if (file_exists($file)) {
            if (file_exists($layout)) {
                /* Render layout */
                ob_start();
                include_once $layout;
                $html = ob_get_clean();

                /* Render view */
                ob_start();
                include_once $file;
                $content = ob_get_clean();

                $html = Directives::parse($this, $html, $content);

                $this->rendered = true;
            } else {
                if(!$this->layout) {
                    /* Render view */
                    ob_start();
                    include_once $file;
                    $content = ob_get_clean();

                    $html = Directives::parse($this, '', $content);

                    $this->rendered = true;
                } else {
                    throw new \Exception("Could not locate the layout '" . basename($layout) . "' in " . dirname($layout));
                }
            }
        } else {
            throw new \Exception("Could not locate the view '" . basename($file) . "' in " . dirname($file));
        }

        if (!empty($html)) {
            $html = $this->app->debugger->injectDebuggerCode($html);

            $this->app->events->trigger('pre-render', $this);
            echo $this->app->filters->trigger('render', $html);
        }
    }

    /**
     *
     * partial
     *
     * Output a partial template (without layout)
     *
     * @param     string     template name
     * @param     mixed      data to pass to the current loop index
     * @return    void
     * @access    public
     * @final
     *
     */
    public final function partial($name, $data=null)
    {
        $addition = null;

        if ($this->app->request->isMobile() && $this->app->config->general->is_reponsive == 'no') {
            $addition = '_mobile';
        }

        if (stristr($name, '.html') || stristr($name, '.php') || stristr($name, '.mustache')) {
            if (file_exists($this->path . '/partials/' . $name)) {
                ob_start();
                include dirname($this->path) . '/partials/' . $name;
                $html = ob_get_clean();
                $html = $this->app->filters->trigger('render', $html);
                echo Directives::parse($this, '', $html);
            } else {
                try {
                    throw new \Exception('Cannot find partial named: ' . $name . ' in ' . $this->path . '/partials/');
                } catch (\Exception $e) {
                    $this->app->debugger->addException($e);
                }
            }
        } else {
            if (file_exists(dirname($this->path) . '/partials/' . $name . $addition . '.html')) {
                ob_start();
                include dirname($this->path) . '/partials/' . $name . $addition . '.html';
                $html = ob_get_clean();
                $html = $this->app->filters->trigger('render', $html);
                echo Directives::parse($this, '', $html);
            } else {
                try {
                    throw new \Exception('Cannot find partial named: ' . $name . ' in ' . $this->path . '/partials/');
                } catch (\Exception $e) {
                    $this->app->debugger->addException($e);
                }
            }
        }
    }

    /**
     *
     * load
     *
     * Load a template file from it's full path  (warning: includes only once)
     *
     * @param   string  file name
     * @access  public
     * @final
     *
     */
    public final function load($file)
    {
        if (file_exists($file)) {
            ob_start();
            include $file;

            $html = ob_get_clean();
            $html = $this->app->filters->trigger('render', $html);
            echo Directives::parse($this, '', $html);
        }
    }

    /**
     *
     * loop
     *
     * A looping version of the partial method
     *
     * @param     string     template name
     * @param     array      array to loop include
     * @return    void
     * @access    public
     * @final
     *
     */
    public final function loop($name, $data)
    {
        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $item) {
                ob_start();
                $this->partial($name, $item);
                $html = ob_get_clean();
                $html = $this->app->filters->trigger('render', $html);
            }
        }
    }
    
    /**
     *
     * Parse shorthand code from the templates mapped to the "$this->data" object
     *
     * @param   string  the html to parse
     * @return  string  the parsed html
     * @access  private
     * @final
     *
     */
    private final function parseShortHandCode($html)
    {
        $regex ='(\\{\\{)(.*)(\\}\\})';
        
        if ($results = preg_match_all("/" . $regex . "/is", $html, $matches)) {
            $res = $matches[2];
            
            foreach ($res as $string) {
                $modifiers = explode("|", $string);
                
                if (count($modifiers) > 1) {
                    /* Handle modifiers */
                    $parts = explode(".", $modifiers[0]);
                    $value = $this->data;
                    
                    foreach ($parts as $key) {
                        $value = $value->{$key};
                    }
                    
                    switch ($modifiers[1]) 
                    {
                        case "upper":
                            $value = (function_exists('mb_strtoupper')) ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
                            break;
                            
                        case "lower":
                            $value = (function_exists('mb_strtolower')) ? mb_strtolower($value, 'UTF-8') : strtolower($value);
                            break;
                            
                        case "slug":
                            $value = $this->app->helpers->text->slug($value);
                            break;
                            
                        case "date":
                            $value = $this->app->html->formatDate($value, false, $_SESSION['app_language'], false, false, true);
                            break;
                            
                        case "datesimple":
                            $value = date('d/m/Y', $value);
                            break;
                            
                        case "fulldate":
                            $value = $this->app->html->formatDate($value, true, $_SESSION['app_language'], false, true, true);
                            break;
                            
                        case "currency":
                        case "money":
                            break;  
                    }
                    
                    $html = str_replace('{{' . $string . '}}', $value, $html);
                } else {
                    $parts = explode(".", $string);
                    $value = $this->data;
                    
                    foreach ($parts as $key) {
                        $value = $value->{$key};
                    }
                    
                    $html = str_replace('{{' . $string . '}}', $value, $html);
                }
            }
        }
        
        return $html;
    }
}

/* Asset type constants */
class AssetTypes
{
    const JS   = "js";
    const CSS  = "css";
    const PNG  = "png";
    const JPG  = "jpg";
    const JPEG = "jpeg";
    const GIF  = "gif";
    const SVG  = "svg";
    const HTML = "html";
    const JSON = "json";
}
