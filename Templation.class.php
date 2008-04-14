<?php
/**
* Templation - Cascading Site Building Tool
* @package Templation
* @author Gabe da Silveira <gabe@websaviour.com>
* @copyright Copyright (c) 2002-2005
* @link http://templation.websaviour.com/ Templation Website
* @license http://opensource.org/licenses/gpl-license.php GNU Public License

     This file is part of Templation.
 
     Templation is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published by
     the Free Software Foundation; either version 2 of the License, or
     (at your option) any later version.
 
     Templation is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.
 
     You should have received a copy of the GNU General Public License
     along with Templation; if not, write to the Free Software
     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

* Templation is a robust HTML construction and maintenance tool 
* based around the idea of cascading repeated elements throughout 
* a website without resorting to a rigid structure that could get 
* in the way of designers and developers. Templation consolidates
* repeated page elements through the use of simple templating and
* inclusion system that is controlled using standard HTML meta tags.
*
* $Id: Templation.class.php,v 1.5 2005/09/05 20:43:25 dasil003 Exp $
*/

require_once "PEAR.php";
require_once "Templation_Widget.class.php";

// Values for mode_vector of the parseTemplate function.
define('TEMPLATION_PARSE_DATA',     1);
define('TEMPLATION_PARSE_WIDGETS',  2);
define('TEMPLATION_PARSE_INCLUDES', 4);

// Define error codes starting with critical errors:
define("TEMPLATION_OK",                         1);
define("TEMPLATION_ERROR",                     -1);
define("TEMPLATION_TEMPLATE_NOT_FOUND",        -2);
define("TEMPLATION_TEMPLATE_UNOPENABLE",       -3);
define("TEMPLATION_BAD_SETTING",               -4);
define("TEMPLATION_WIDGET_ERROR",              -5);

// Warnings usually indicate error conditions which result in incorrect output:
define("TEMPLATION_WARNING",                   -100);
define("TEMPLATION_INCLUDE_NOT_FOUND",         -101);
define("TEMPLATION_INCLUDE_UNOPENABLE",        -102);
define("TEMPLATION_WIDGET_NOT_FOUND",          -103);
define("TEMPLATION_WIDGET_UNOPENABLE",         -104);
define("TEMPLATION_INVALID_PATH",              -105);
define("TEMPLATION_INVALID_FILE_OBJECT",       -106);
define("TEMPLATION_WIDGET_WARNING",            -107);
define("TEMPLATION_FOLDER_UNOPENABLE",         -108);
define("TEMPLATION_ACTION_NOT_FOUND",          -109);

// Notices are anomolous conditions which may be benign.
define("TEMPLATION_NOTICE",                    -1000);
define("TEMPLATION_NOT_ENOUGH_INCLUDES",       -1001);
define("TEMPLATION_WIDGET_NOTICE",             -1002);


class Templation extends PEAR
{
    /** If a critical error occurs, this should be set to true.
    * @access private
    * @var bool
    */
    var $invalid = false;
    /** This is the Templation data repository, it is passed to the 
    * constructor or auto-detected.
    * @var string
    */
    var $tpl_dir = '';
    
    /*** GLOBAL CONFIG ONLY SETTINGS ***/
    var $tpl_dir_local = '';        //An alternate data repository (not including cache), $DOCUMENT_ROOT/_data/ by default.
                                    //This would be a site-specific setting except it needs to be set first in order to FIND the 
                                    //site-specific configuration.
    var $hosts = array();           //Array of site specific configurations hashed on the host variable.  This is more limited
                                    //than standard site-specific configuration file, but is useful if you want to make all your
                                    //site definitions in one file.
    
    /*** SITE SPECIFIC CONFIG SETTINGS ***/
    var $root = '';                 //HTTP Server Root, may be set in settings.php or derived automagically.
    var $host = '';                 //Hostname (or IP), may be set arbitrarily in config.  There should be
                                    //one unique host for each unique site (even if multiple domains point to it),
                                    //otherwise multiple caches of the same site will be created.
    var $src_name = 'SCRIPT_NAME';  //The $_SERVER variable that gives the source name relative to document root. 
    var $src_filename = '';         //If set will override src_name and provide a full path to the actual source page.
    
    var $caching_on = false;         //Caching minimizes processing, but sometimes needs to be turned off for debugging.
    
    var $includes_dir = 'includes'; //Name of directory to search for meta file
    var $meta_name = 'meta.php';    //Filename to parse for meta tags.
    
    var $template_mode = 'std';     //The symbol over which alternate versions of the same template 
                                    //may branch. Source file is not affected, but templates and filters can be.
    var $widgets = array();         //Associative array of Templation_Widget objects hashed on widget_path to avoid 
                                    //duplicate widget instances, thus allowing for widgets to contain static values.
    var $pages = array();           //Array of Templation_File objects.  See instantiation in class constructor.
    
    /*** ARRAYS OF SEARCH PATHS ***/
    var $widget_dirs = array();     
    var $template_dirs = array();
    var $action_dirs = array();

    /*** ERROR HANDLING ***/
    var $errors = array();          //Array of PEAR errors that occurred during processing (if mode contains PEAR_ERROR_RETURN).
    var $error_log = '';            //Full path filename to a Templation error log.
    var $error_function = '';       //A callback function for Templation errors, should follow the PHP pseudo-type format for a function or class method.
    
    /*** AUXILIARY VARIABLES not used by Templation directly (recommended for widget use) ***/
    var $index_filenames = array('index.php','index.html'); //Useful information when it comes to link recognition.
    
    /**
    * Initialize Templation settings and a Templation_File for the requested (or specified) page.
    *
    * @param string The location of the Templation data repository.
    * @param string The path (relative to document root) to the desired file.
    **/
    function Templation($tpl_dir = '', $file_path = '')
    {
        /*** PEAR Constructor ***/
        $this->PEAR();
        
        
        /*** SET UP GLOBAL SETTINGS ***/
        if($tpl_dir == '') {
            $p = strrpos(__FILE__,'/');
            $tpl_dir = substr(__FILE__,0,$p).'/data';
        }
        
        $this->tpl_dir = $tpl_dir;
        $this->cache_dir = $tpl_dir.'/cache'; //There is only one cache, but everything else has multiples.
        $this->widget_dirs = array($tpl_dir.'/widgets');
        $this->template_dirs = array($tpl_dir.'/templates');
        $this->action_dirs = array($tpl_dir.'/actions');
        
        //Default site settings which may be overridden.
        $this->host = $_SERVER['HTTP_HOST'];
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        if(is_dir($this->root.'/_data')) $this->tpl_dir_local = $this->root.'/_data';
        if(is_file($this->tpl_dir.'/settings.php')) include_once($this->tpl_dir.'/settings.php');
        
        
        /*** SET UP SITE SPECIFICS ***/
        if($this->tpl_dir_local && is_dir($this->tpl_dir_local.'/actions')) array_push($this->action_dirs, $this->tpl_dir_local.'/actions');
        if($this->tpl_dir_local && is_dir($this->tpl_dir_local.'/templates')) array_push($this->template_dirs, $this->tpl_dir_local.'/templates');
        if($this->tpl_dir_local && is_dir($this->tpl_dir_local.'/widgets')) array_push($this->widget_dirs, $this->tpl_dir_local.'/widgets');
        
        if($this->tpl_dir_local && is_file($this->tpl_dir_local.'/settings.php')) include_once($this->tpl_dir_local.'/settings.php');
        
        
        /*** SET UP ERROR HANDLING ***/
        if(!empty($this->error_function)) $this->setErrorHandling(PEAR_ERROR_CALLBACK, $this->error_function);
        else $this->setErrorHandling(PEAR_ERROR_PRINT, "<p><strong>Templation Error:</strong> %s</p>");
        
        
        /*** DEFAULT PAGE ***/
        if($file_path == '') {
            if($this->src_filename) $file_path = $_SERVER[$this->src_filename];
            else $file_path = $this->root.$_SERVER[$this->src_name];
            $file_path_autodetected = true;
        } else {
            $file_path_autodetected = false;
        }
        
        
        /*** ERROR CHECKING ***/
        if(!is_dir($this->tpl_dir)) {
            $this->errors[] = $this->raiseError("Initialization error: The specified data repository '<code>{$this->tpl_dir}</code>' is not a directory.", TEMPLATION_BAD_SETTING);
            $this->invalid = true;
            return;
        }
        
        if(!is_dir($this->root)) {
            $this->errors[] = $this->raiseError("Initialization error: The specified site root '<code>{$this->root}</code>' is not a directory.", TEMPLATION_BAD_SETTING);
            $this->invalid = true;
            return;
        } elseif(!is_file($file_path)) {
            $this->errors[] = $this->raiseError("Initialization error: The specified file '<code>{$file_path}</code>'".($file_path_autodetected ? " (auto-detected from URL)" : '')." is not a file.", TEMPLATION_BAD_SETTING);
            $this->invalid = true;
            return;
        }

        /*** INITIAL PAGE SETUP, won't be built until output() is called on it ***/
        $this->pages[] = new Templation_File($this,$file_path);
    }
    
    function output($page_num = 0)
    {
        if ( isset($this->pages[$page_num]) 
        && Templation_File::isTemplationFile($this->pages[$page_num]) ) {
            return $this->pages[$page_num]->outputFile();
        } else {
            $this->errors[] = $this->raiseError("output(): Requested page doesn't exist", TEMPLATION_INVALID_FILE_OBJECT);
            return false;
        }
    }

    
    /**
    * Takes a reference to a template string and parses values directly into it according to what the
    * mode_vector specifies (currently data, includes, and widgets).  The src_page is an optional parameter
    * which is used to provide a starting point for the recursive inclusion capabilities.  As the main parsing 
    * engine of Templation, this function is set up so that it can be used independently of the automatic
    * page building functionality.  If you leave out the source page, the function will include files
    * from the base include directory found in the Templation directory itself.  Specifying an include search
    * path could be useful, but the core of this class is its cascading parameters and not the template engine itself.
    * Please note that src_page should be passed by reference so that addDependency() works as expected.
    *
    **/
    function parseTemplate(&$template, $data, &$src_page, $mode_vector = 7)
    {        
        if (Templation_File::isTemplationFile($src_page)) {
            $using_src_page = true;
            $inc_dir = $src_page->tpl->root.'/'.$src_page->rel_dir; //Initial includes directory for recursive search.
            $userinfo['Source Page'] = $src_page->full_path;        //For error reporting.
        } else {
            $using_src_page = false;
            $inc_dir = $this->tpl_dir.'/'.$this->includes_dir;      //Default includes come from Templation directory.
            $userinfo['Source Page'] = "unspecified";               //For error reporting.
        }
                        
        //Process custom tags
        preg_match_all('/<%%[^%]+%%>/i',$template,$matches);
        foreach ($matches[0] as $tag) {
            $tag_value = preg_replace('/<%%([^%]+)%%>/','\1',$tag);
            $values = preg_split('/\s+/',$tag_value);

            //Tags with a single symbol will be replaced directly with values from the data array.
            if (count($values) == 1 && ($mode_vector & TEMPLATION_PARSE_DATA)) {
                if (!isset($data[$values[0]])) $data[$values[0]] = '';
                $template = str_replace($tag,$data[$values[0]],$template);

            //<&&inc filename&&> is shorthand for the tpl_include.php widget.  It did not start this way
            //but it made sense to make the widget conversion once the widget API was available.
            } else if ($values[0] == 'inc' && ($mode_vector & TEMPLATION_PARSE_INCLUDES)) {
                $widget = 'tpl_include.php';
                $argv = array_slice($values,1);
                $widget_out = $this->_processWidget($widget,$argv,$data,$src_page);
                $template = str_replace($tag,$widget_out,$template);
                
            //<&&tpl filename&&> is shorthand for the tpl_template.php widget.
            } else if ($values[0] == 'tpl' && ($mode_vector & TEMPLATION_PARSE_WIDGETS)) {
                $widget = 'tpl_template.php';
                $argv = array_slice($values,1);
                $widget_out = $this->_processWidget($widget,$argv,$data,$src_page);
                $template = str_replace($tag,$widget_out,$template);
                
            //Here we process widgets.  Widgets are basically code blocks that execute in-line (right here) as opposed to
            //includes which are simply output into the cached copy (ie. code does not execute until page is finally served).
            //Because widgets operate within Templation, they are all stored in a single directory.
            } else if ($mode_vector & TEMPLATION_PARSE_WIDGETS) {
                $widget = $values[0];
                $argv = array_slice($values,1);
                $widget_out = $this->_processWidget($widget,$argv,$data,$src_page);
                $template = str_replace($tag,$widget_out,$template);
                
             //This implies the tag in question is not a type currently being parsed, strip the tag.
            } else {
                $template = str_replace($tag,'',$template);
            } 
        }
    } //end parseTemplate()

    /**
    * This function checks for existence of a widget and instantiates it if it hasn't been
    * already ($this->widgets is hashed on the widget filename).  Then it returns the widget's
    * output.
    **/
    function _processWidget($widget, $argv, $data, &$src_page) 
    {
        foreach($this->widget_dirs as $path) {
            if(is_file($path."/".$widget)) $widget_path = $path."/".$widget;
        }
        
        if (!isset($widget_path)) {
            $this->errors[] = $this->raiseError("_processWidget(): Widget '$widget' does not exist", TEMPLATION_WIDGET_NOT_FOUND);
        } else {
            if(!isset($this->widgets[$widget])) {
                $this->widgets[$widget] = new Templation_Widget($widget_path, $argv, $data, $src_page);
                $src_page->addDependency($widget_path);
            } else {
                $this->widgets[$widget]->_reInit($argv, $data, $src_page);
            }
            return $this->widgets[$widget]->output();
        }
        
        return false;
    }

/*** STATIC FUNCTIONS CAN BE INSTALLED WITH INSTANTIATION ***/
    function errorMessage($value)
    {
        static $errorMessages;
        if (!isset($errorMessages)) {
            $errorMessages = array(
                TEMPLATION_OK                   => 'no error',
                TEMPLATION_ERROR                => 'error: unknown error',
                TEMPLATION_TEMPLATE_NOT_FOUND   => 'error: template not found',
                TEMPLATION_TEMPLATE_UNOPENABLE  => 'error: template unopenable',
                TEMPLATION_BAD_SETTING          => 'error: bad setting',  
                TEMPLATION_WARNING              => 'warning: generic',      
                TEMPLATION_INCLUDE_NOT_FOUND    => 'warning: include not found',
                TEMPLATION_INCLUDE_UNOPENABLE   => 'warning: include unopenable',
                TEMPLATION_WIDGET_NOT_FOUND     => 'warning: widget not found',
                TEMPLATION_WIDGET_UNOPENABLE    => 'warning: widget unopenable',
                TEMPLATION_INVALID_PATH         => 'warning: invalid path',
                TEMPLATION_INVALID_FILE_OBJECT  => 'warning: invalid file object',
                TEMPLATION_WIDGET_ERROR         => 'warning: widget error',
                TEMPLATION_FOLDER_UNOPENABLE    => 'warning: folder unopenable',
                TEMPLATION_NOTICE               => 'notice: generic'
            );
        }
        
        if (Templation::isError($value)) {
            $value = $value->getCode();
        }
        
        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[TEMPLATION_ERROR];
    }
    
    /**
    * This function accepts a comma-delimited list of meta values to be converted to an array.  
    * Commas may be escaped, and backslashes may be escaped (to handle the case of a backslash
    * occurring just before a comma).  Other backslashes will be output literally.
    **/
    function metaToArray($string, $trim = true) 
    {
        if(is_array($string)) return $string;
        if(is_bool($string) || is_int($string)) return array($string);
        
        $out = array('');
        $escaped = false;
        $j = 0;
        for($i=0; $i<strlen($string); $i++) {
            if($escaped) {
                if($string{$i} == ',' || $string{$i} == '\\') $out[$j] .= $string{$i};
                else $out[$j] .= '\\'.$string{$i};
                $escaped = false;
            } else {
                if($string{$i} == '\\' && $i != strlen($string) - 1) $escaped = true;
                elseif($string{$i} == '\\') $out[$j] .= '\\';
                elseif($string{$i} == ',') $out[++$j] = '';
                else $out[$j] .= $string{$i};
            }
        }
        
        array_walk($out,'trim');
        
        return $out;
    }
} //end class



class Templation_File 
{
    var $tpl = '';                  //The calling object.  Useful for certain variables such as root.
    
    var $rel_path = '';             //Path to source file starting at $cms->root, leading slash.
    var $rel_dir = '';              //Path to directory that source file resides in.
    var $full_path = '';            //Full path for use in file functions.
    var $dirs = array();            //All directories between root and current file.  Note index is one off from deep_meta.
    
    var $deep_meta = array(array());//$deep_meta[0] = base_dir meta, [n] = file meta.
    var $file_meta = array();
    var $data = array();            //The flattened version of $deep_meta.
    
    var $cache_path = '';           //Full path of cache file (branches on tpl->host and tpl->template_mode)
    var $dependencies_path = '';    //Full path of dependency list cache.
    
    var $dependencies = array();    //Filenames of files used.
    
    var $parse_me;                  //Indication whether page was parsed or if output is just raw file contents (depends on parse_me).
    var $output = '';               //Final page output to be included, must be accessed via getOutput() or may be empty.
    
    
    /**
    * Constructor
    **/
    function Templation_File(&$tpl, $filepath)
    {

        //First check that file exists and is within root
        $this->tpl =& $tpl;
        $this->rel_path = substr($filepath,strlen($this->tpl->root));
        $this->rel_dir = substr_replace($this->rel_path, '', strrpos($this->rel_path,'/') + 1);
        $this->full_path = $filepath;
        
        $this->cache_path = $this->tpl->cache_dir.'/'.$this->tpl->host.'/'.$this->tpl->template_mode.$this->rel_path;
        $this->dependencies_path = substr_replace($this->cache_path,'_dependencies_',strrpos($this->cache_path,'/')+1,0);
        
        if(strlen($this->rel_dir) > 1) $this->dirs = explode('/',trim($this->rel_dir,'/')); //If not at root level.
        
        $this->gatherMeta();
        
        //flatten data
        foreach ($this->deep_meta as $tempData) {
            while (list($key,$val) = each($tempData)) {
                $this->data[$key] = $val;
            }
        }
        
        // If parse by default is true and parse_me is not false
        // OR if parse_me is true then parse, otherwise don't parse.
        if  (isset($this->data['parse_me']) && $this->data['parse_me']) {
            $this->parse_me = true;
        } else {
            $this->parse_me = false;
        }   
        
        
    }
    
    
    /**
    * Tha heavy lifting.
    * Uses cached copy if it exists and is current, otherwise builds anew.
    **/
    function buildPage()
    {
        if ( !$this->cacheValid() ) {
            
            //If parse_me is not true or there is an error reading the template, then just output file.
            if ( !$this->parse_me || (!$this->_readTemplate($this->output, $this->data['template'])) ) {
                $fp = fopen($this->full_path, "r");
                $contents = fread($fp, filesize($this->full_path));
                fclose ($fp); 
                $this->output = preg_replace('/include(_once)?\([^)]+\);?/','',$contents,1);
                $this->parsed = false;
            } else { //This implies success of _readTemplate() and we can then parse it.
                $this->tpl->parseTemplate($this->output, $this->data, $this);
                $this->_runActions();
                $this->parsed = true;
            }
            
            $this->validateCachePath();
            $oldumask = umask(0);
            $fp = fopen($this->cache_path,"w");
            fwrite($fp, $this->output);
            fclose($fp);
            
            $d = implode("\n",$this->dependencies);
            $fp = fopen($this->dependencies_path,"w");
            fwrite($fp, $d);
            fclose($fp);
            umask($oldumask);
        }
    }    


    /**
    * Output processed page.
    **/
    function outputFile()
    {
        if ( empty($this->output) ) {
            $this->buildPage();
            if ( empty($this->output) ) $this->output = 'Empty Page';
        }
        
        return $this->cache_path;
    }
    
    
    /**
    * Recursively gather array from base_dir to rel_path from $includes_dir/$meta_filename.
    **/
    function gatherMeta()
    {
        //initialize values for loop
        $temp_dir = $this->tpl->root;
        $d = $this->dirs;
        if (!isset($this->dirs[0]) || $this->dirs[0] !== '') $d = array_merge(array(''),$d); //Blank entry simplified loop setup.
        
        //gather meta information from appropriate sources
        //Adding dependencies should be done here.  Simply adding all $fnames would not work because
        //if a file is deleted, there is no record that it ever existed.  On the other hand, if we only
        //add $fnames that exist, then if new meta files are added in the hierarchy, there will be no dependency
        //recorded for that file and hence it will be ignored by a strict dependency modified-date check.
        //Some special check will be necessary.
        
        //First get meta data from data/includes:
        $fname = $this->tpl->tpl_dir.'/'.$this->tpl->includes_dir.'/'.$this->tpl->meta_name;
        if(is_file($fname)) {
            $this->dependencies[] = $fname;
            $this->deep_meta[0] = get_meta_tags($fname); //Beware get_meta_tag bugs.
            array_walk($this->deep_meta[0],array($this,'metaValueParser'));
        } else {
            $this->dependencies[] = $fname." 0";
            $this->deep_meta[0] = array();
        }
        
        //Second get meta data from all the website directories.
        $i = 1;
        foreach($d as $thang) {
            if ($thang != '') $temp_dir .= '/'.$thang;
            $fname = $temp_dir.'/'.$this->tpl->includes_dir.'/'.$this->tpl->meta_name;
            if (is_file($fname)) {
                $this->dependencies[] = $fname;
                $this->deep_meta[$i] = get_meta_tags($fname); //Beware get_meta_tag bugs.
                array_walk($this->deep_meta[$i], array($this,'metaValueParser'));
            } else {
                $this->dependencies[] = $fname." 0";
                $this->deep_meta[$i] = array();
            } 
            
            $i++;
        }
        
        //Third get meta from the actual file.
        $this->deep_meta[$i] = get_meta_tags($this->full_path);
        array_walk($this->deep_meta[$i], array($this,'metaValueParser'));
        
        // Read in standard pagebuilding elements.  
        //  leading_php - Code that needs to send headers must occur before output, so it can be
        //                 inserted in the first PHP block before the <html> element.
        //  head - Standard <head> element contents.
        //  body - Standard <body> element contents.
        //  frameset - Standard <frameset> element instead of <body>.
        // 
        // Beware the regexp method of pulling these elements.  In particular, the sequence
        // <html should only occur once, otherwise the first regexp will pull more than intended.
        $fp = fopen($this->full_path, "r");
        $content = fread($fp, filesize($this->full_path));
        fclose($fp);
        
        //Note that the php must be the first thing in the document, also, this is a naive pattern matcher.  
        //If you have a string containing the PHP closing tag then the final output will be mysteriously buggy.
        //Note that there are some added slashes to prevent BBEdit syntax highlighting issues related to closing PHP tags.
        preg_match('/^(<\?php.*?\?>)/is', $content, $matches);
        if (isset($matches[1])) {
            //Strip out the first include() as that is the one that is used to invoke the driver program.
            $raw_php = $matches[1]; //The length of this is used below for finding where the body starts in absense of body tags.
            $this->deep_meta[$i]['leading_php'] = preg_replace('/include(_once)?\([^;\n]+\);?/','',$raw_php,1);
            if(preg_match('/^<\?php\s*\?>$/s',$this->deep_meta[$i]['leading_php'])) $this->deep_meta[$i]['leading_php'] = '';
        } else {
            $this->deep_meta[$i]['leading_php'] = '';
        }
        if (preg_match("/<body[^>]*>(.*)<\/body>/is", $content, $matches)) {
            $this->deep_meta[$i]['body'] = $matches[1];
        } else {
            //If no body tag than the entire thing (- leading_php) is the body.
            $this->deep_meta[$i]['body'] = substr($content, strlen($raw_php));
        }
        if (preg_match("/<head[^>]*>(.*)<\/head>/is", $content, $matches)) {
            $this->deep_meta[$i]['head'] = $matches[1];
        }
        if (preg_match("/<frameset[^>]*>(.*)<\/frameset>/is", $content, $matches)) {
            $this->deep_meta[$i]['frameset'] = $matches[1];
        }
        
        $this->file_meta = $this->deep_meta[$i];
    }

    /**
    * This function is a helper used by array_walk() in gather_meta() to convert the string 'false' and 'true' to 
    * boolean values as well as integer values (no spaces!) to actual PHP ints.  In beta 2, comma-delimited lists
    * were parsed automatically, but that complicated matters so both scalar and array values needed to be accounted
    * for.  That functionality has been added to Templation_Widget::metaToArray().
    **/
    function metaValueParser(&$string, $index) 
    {
        if($string=="true") $string = true;
        else if($string=="false") $string = false;
        else if(preg_match('/^\d+$/',$string)) $string = intval($string);
        else $string = strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
        //print "$string!<br />\n";
        return $string;
    }
    
    
    /**
    * This function must be called with the full pathname of every single page that is included or read
    * or influences the construction of a page in any way otherwise the caching mechanism will not know
    * when a page needs to be rebuilt.
    **/
    function addDependency($dependency)
    {
        $this->dependencies[$dependency] = $dependency;
    }
    
    
    /**
    * This function returns true if the cache file exists and was modified more recently
    * than the file itself or any of its dependencies.  Otherwise false.
    **/
    function cacheValid() 
    {
        if ( (!is_file($this->cache_path)) || (filemtime($this->full_path) > ($cache_timestamp = filemtime($this->cache_path)) || !$this->tpl->caching_on) ) return false;
        
        //Need to check for existence of file
        if ( $fp = fopen($this->dependencies_path, "r") ) {
            $c = fread( $fp, filesize($this->dependencies_path) );
            fclose($fp);
            
            $dependencies = preg_split("/(\r\n)|(\r)|(\n)/",$c,-1,PREG_SPLIT_NO_EMPTY);
            
            foreach ($dependencies as $string) {
                unset($matches);
                if ( preg_match("/([^\s]+)\s*(\d*)/",$string,$matches) == 1 ) {
                    //0 indicates file did not exist at the time.
                    if ($matches[2] == '0') {
                        if ( is_file($matches[1]) ) return false;
                    } else {
                        if ( $cache_timestamp < filemtime($matches[1]) ) return false;
                    }
                }
            }
            
            return true;
        } else {
            //error opening file.
        }
        
        return false;
    }
    
    
    /**
    * If $this->cache_path is not valid, this function will create directories as needed to validate
    * the path.  If the last piece of the path doesn't contain a period this function
    * assumes the path does NOT end with a file name.
    **/
    function validateCachePath() {
        $relative_path = str_replace($this->tpl->cache_dir,'',$this->cache_path);
        $path_array = explode('/',$relative_path);
        
        // Drop the filename part of the path if it is there
        if(strchr($path_array[sizeof($path_array)-1], "."))
            $path_array[sizeof($path_array)-1] = "";
            
        $path = $this->tpl->cache_dir;
        foreach($path_array as $p) {
            if($p != "")
                $path .= "/$p";
        }
        if(is_dir($path))
            return;
    
        $i = 0;
        $path = $this->tpl->cache_dir;
        $newpath = false;
        $oldumask = umask(0);
        while(isset($path_array[$i])) {
            if($path_array[$i] != "") {
                $path .= "/" . $path_array[$i];
                
                if($newpath || !is_dir($path)) {
                    $newpath = true; // from here on out the path isn't going to exist
    
                    if(!mkdir($path, 0777))
                        trigger_error("'$path' is unwritable by server.", E_USER_ERROR);
                }
            }
    
            $i++;
        }
        umask($oldumask);
    }
    
    
    /**
    * Statically checks to see if an object is of this class.
    **/
    function isTemplationFile($obj) {
        return is_a($obj,'Templation_File'); //Requires PHP 4.2+
    }
    
    
    /**
    * Opens and reads a template file from the standard templates directory using 
    * the template_mode subdirectory if necessary.
    *
    **/
    function _readTemplate(&$template, $filename) {
        $userinfo['Source Page'] = $this->full_path; //For error reporting purposes.
                
        foreach($this->tpl->template_dirs as $path) {
            if(is_file($path.'/'.$this->tpl->template_mode.'/'.$filename)) $template_filename = $path.'/'.$this->tpl->template_mode.'/'.$filename;
            else if(is_file($path.'/'.$filename)) $template_filename = $path.'/'.$filename;
        }

        if (!isset($template_filename)) {
            $this->tpl->errors[] = $this->tpl->raiseError("_readTemplate(): Unsuccessful finding '$filename'", TEMPLATION_TEMPLATE_NOT_FOUND, null, null, serialize($userinfo));
        } else if ( ($fp = fopen($template_filename, 'r')) === false) {
            $this->tpl->errors[] = $this->tpl->raiseError("_readTemplate(): Unsuccessful opening '$filename'", TEMPLATION_TEMPLATE_UNOPENABLE, null, null, serialize($userinfo));
        } else {
            $this->addDependency($template_filename);
            $template = fread($fp, filesize($template_filename));
            return true;
        }
        
        return false;
    } //end _readTemplate()
    
    
    
    /**
    * This function runs all the actions specified (if they exist).
    **/
    function _runActions() {
        if (isset($this->data['actions'])) {
            $actions = Templation::metaToArray($this->data['actions']);
            foreach($actions as $a) {
                $action_path = '';
                foreach($this->tpl->action_dirs as $path) {
                    if(is_file($path.'/'.$a)) $action_path = $path.'/'.$a;
                }
                if(!$action_path) $this->tpl->errors[] = $this->tpl->raiseError("_runActions(): Action '$a' not found.", TEMPLATION_ACTION_NOT_FOUND);
                else include($action_path);
            }
        }
    } //end _runActions()
}
?>
