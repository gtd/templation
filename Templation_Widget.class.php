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

* The Templation_Widget class defines an API for widget writers to use
* in the development of custom widgets.  Please see the documentation
* to understand the widget concept and best practices when developing them.
*
* $Id: Templation.class.php,v 1.5 2005/09/05 20:43:25 dasil003 Exp $
*/

require_once("Templation.class.php");

class Templation_Widget {
    var $widget;
    var $widget_path;
    var $argv;
    var $data;
    var $src_page;
    var $statics = array(); //Hash of widget 'static' values.
    
    function Templation_Widget($wp,$a,$d,&$sp) {
        $this->widget = substr(strrchr($wp, "/"), 1);
        $this->widget_path = $wp;
        $this->argv = $a;
        $this->data = $d;
        $this->src_page =& $sp;
    }
    
    function output() {
        $output = null;
        $data =& $this->data;
        $argv =& $this->argv; 
        include($this->widget_path);
        return $output;
    }

    /**
    * This function checks for the existence of the given file, raising a Templation_Error if
    * it doesn't exist.  Then it adds the dependency for caching purposes and reads the file in.
    * NEVER USE readfile() OR include() DIRECTLY TO AVOID CORRUPTING THE CACHE.
    **/
    function getInclude($file) {
        $tpl =& $this->src_page->tpl;
        
        if (!is_file($file)) {
            throw new Exception("getInclude(): Include file '$file' is not valid", TEMPLATION_INCLUDE_NOT_FOUND);
        } else if (!$fp = fopen($file, 'r')) {
            throw new Exception("getInclude(): Error opening '$file'", TEMPLATION_INCLUDE_UNOPENABLE);
        } else {
            $this->src_page->addDependency($file);
            return fread($fp, filesize($file));
        }

        return false;
    }

    /**
    * Given a filename and a starting directory within the site root, this function searches backwards
    * through each directory level looking for the specified file in the includes directory as named
    * in the configuration.  Finally it checks the default location in the Templation directory.  If no 
    * such file is found then it returns false.
    **/
    function findIncludes($file, $num = 1, $dir = null) {
        $tpl =& $this->src_page->tpl;
        $paths = array();
        
        if (!isset($dir)) {
            $dir = $tpl->root . substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/'));
        }
        
        if (substr($dir,0,strlen($tpl->root)) != $tpl->root) {
            throw new Exception("findIncludes(): The directory '$dir' is not within the site root", TEMPLATION_INVALID_PATH);
            return false;
        }
        
        $path = $dir.'/'.$tpl->includes_dir.'/'.$file;
         if(is_file($path)) {
             $paths[] = $path;
             $num--;
        }
        
        while ($dir != $tpl->root && $num > 0) {
            $dir = substr($dir, 0, strrpos($dir, '/'));
            $path = $dir.'/'.$tpl->includes_dir.'/'.$file;
            if(is_file($path)) {
                $paths[] = $path;
                $num--;
            }
        }
        
        //The root include would have been skipped by above loop semantics.
        if($num > 0 && is_file($path)) {
            $paths[] = $path; 
            $num--;
        }
        
        //Finally check the data repository if necessary.
        if($num > 0 && is_file($path = $tpl->tpl_dir.'/'.$tpl->includes_dir.'/'.$file)) {
            $paths[] = $path;
            $num--;
        }
        
        /*if($num > 0) {
            throw new Exception("findIncludes(): Less than the requested number of includes were returned", TEMPLATION_NOT_ENOUGH_INCLUDES);
        }*/
        
        foreach($paths as $p) {
            $this->src_page->addDependency($p);
        }
        
        return $paths;
    }
    
    function getTemplate($filename) {
        $this->src_page->_readTemplate($output, $filename);
        return $output;
    }
    
    function parseTemplate($template, $data = null) {
        if(!isset($data)) $data = $this->data;
        $this->src_page->tpl->parseTemplate($template, $data, $this->src_page);
        return $template;
    }
    
    function subWidget($widget, $argv = null, $data = null) {
        if(!isset($data)) $data = $this->data;
        if(!isset($argv)) $argv = $this->argv;
        return $this->src_page->tpl->_processWidget($widget, $argv, $data, $this->src_page);
    }
    
    function metaToArray($string, $trim = true) {
        return Templation::metaToArray($string, $trim);
    }
    
    function setStatic($key,$val) {
        $this->statics[$key] = $val;
    }
    
    function getStatic($key) {
        return $this->statics[$key];
    }
    
    
    /**
    * Reinitializes the widget with new arguments, data and source page so that
    * it can run again with different parameters.
    **/
    function _reInit($a,$d,&$sp) {
        $this->argv = $a;
        $this->data = $d;
        $this->src_page =& $sp;
    }
}
?>