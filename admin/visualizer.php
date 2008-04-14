<?php
/**
* Templation - Cascading Site Building Tool
* Copyright (c) 2002-2005  Gabe da Silveira
* http://templation.websaviour.com/
* email: gabe@websaviour.com
*
*
*    This file is part of Templation.
*
*    Templation is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    Foobar is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with Foobar; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*
* This file contains the templation visualizer.  A small application for printing
* out site hierarchies and associated templation config.  
* 
* IMPORTANT, PLEASE BE AWARE OF THE FOLLOWING:
*
* - This tool will check for the presence of an include statement in your file that 
*   ends with the filename driver.php to determine if the page is even running Templation.
*   If you rename your driver script then it will list all files as unparsed.
*
* Planned enhancements include:
*
* - Built-in meta-tag editor.
* - Flagging redundant tags.
*
* Please see the first few lines for necessary changes depending on your installation.
*
* $Id: visualizer.php,v 1.3 2005/09/05 20:43:25 dasil003 Exp $
**/
ini_set('max_execution_time',15);
define('DEBUG',false);

include_once('Templation/Templation.class.php'); //The Templation Class
include_once('functions.inc.php'); //For select_from_menu() and check_from_menu()
$tpl = new Templation();

//Configuration Stored in Session
if(isset($_SESSION['tpl_visualizer'])) $cfg = $_SESSION['tpl_visualizer'];

//Set defaults.
if(!isset($cfg['site'])) {
    if(isset($tpl->hosts) && !empty($tpl->hosts)) {
        list($site,$arr) = each($tpl->hosts);
        reset($tpl->hosts);
        $cfg['site'] = $site;
        $cfg['root'] = $arr['root'];
    } else {
        $cfg['site'] = $tpl->host;
        $cfg['root'] = $tpl->root;
    }
    $cfg['show_empty_dirs'] = true;
    $cfg['show_non_parsed'] = true;
    $cfg['show_parsed'] = true;
    $cfg['exclude_extensions'] = true; //Needs implementation.
    $cfg['exclude_non_extensions'] = true; //ditto
    $cfg['exclude_non_metafied'] = false;
    $cfg['depth'] = 0;
    $cfg['show'] = array('parsed');
    $cfg['url'] = '/';
}

if (isset($_REQUEST['url'])) $cfg['url'] = stripslashes($_REQUEST['url']);
if($cfg['url'] == '') $cfg['url'] = '/';

if (isset($_REQUEST['site'])) {
	$cfg['site'] = stripslashes($_REQUEST['site']);
	$cfg['root'] = $tpl->hosts[$cfg['site']]['root'];
}
if (isset($_REQUEST['depth'])) $cfg['depth'] = stripslashes($_REQUEST['depth']);
if (isset($_REQUEST['show']) || isset($_POST['url'])) {
	$cfg['show'] = array();
	$cfg['show'] = $_REQUEST['show'];
	$cfg['show_empty_dirs'] = (in_array('empty',$cfg['show'])) ? true : false;
	$cfg['show_non_parsed'] = (in_array('unparsed',$cfg['show'])) ? true : false;
	$cfg['show_parsed'] = (in_array('parsed',$cfg['show'])) ? true : false;
}

$cfg['file_extensions'] = array('.php','.phtml');

$form = build_formvals();

$_SESSION['tpl_visualizer'] = $cfg;
 
//BUILD THE TREE!!!
if(is_file($f = $tpl->tpl_dir.'/'.$tpl->includes_dir.'/'.$tpl->meta_name)) {
   $meta_base = get_meta_tags($f);
} else {
   $meta_base = array();
}
$site_tree = load_sitemap($cfg['root'].'/',$meta_base); //Add caching later?
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Templation Administration - Directory Visualizer</title>
	<link rel="stylesheet" href="admin.css" type="text/css" media="all" />
</head>
<body>
<h1>Templation Administration - Directory Visualizer</h1>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<table id="cPanel" cellspacing="0">
<?php if ( $form['site'] ) { ?>
<tr>
	<td>Site:</td>
	<td><?php echo $form['site'] ?></td>
</tr>
<?php } ?>
<tr>
	<td>Base URL:</td>
	<td><input type="text" name="url" value="<?php echo $form['url'] ?>"></td>
</tr><tr>
	<td>Depth:</td>
	<td><?php echo $form['depth'] ?></td>
</tr><tr>
	<td>Show:</td>
	<td><?php echo $form['show'] ?></td>
</tr><tr>
	<td></td>
	<td><input type="submit" name="submit" value="Refresh"></td>
</tr></table>
</form>

<?php
	
// If we're starting from a subdirectory then print the parent directory info
// before printing the meat and potatoes.
if(strlen($cfg['url']) > 1) {
	echo "<h2>Visualizing Structure of {$cfg['site']}{$cfg['url']}</h2>";
	echo "<p><a href=\"{$_SERVER['PHP_SELF']}?url=/\">&lt;--Back to Site Root</a></p>";
	
	$directories = explode('/',trim($cfg['url'], '/'));
	print "<table cellspacing=\"0\"><tr><td>\n"
	.recursive_prefix($site_tree,$cfg['url'])
	."</td></tr></table>\n\n";
	
	//Pull and print the appropriate subtree.
	$a = $site_tree;
	foreach($directories as $d) {
		$a = $a['dir'][$d]['subdir'];
	}
	
} else {
	echo "<h2>Visualizing Structure of {$cfg['site']}</h2>";
	
	$a = $site_tree;
}

print color_key();
print display_sitemap($a,$cfg['depth'],'topTable');

if(DEBUG) {print "<pre>"; print_r($site_tree); print "</pre>";} 
?>

</body>
</html>
<?php

/*
Recursive function that takes in a directory (generally a site root directory)
*/
function load_sitemap($dir,$meta_data=array(),$prefix='',$includes_dir='includes',$meta_name='meta.php') {
	global $cfg;
	
	$current_path = $prefix.$dir; //The directory that we are examining.
	
	$tree['filename'] = $dir;
	$tree['url'] = substr($prefix.$dir,strlen($cfg['root']));
	
	$meta_file = $current_path.$includes_dir.'/'.$meta_name;
	
	if(is_file($meta_file)) {
        $tree['meta'] = get_meta_tags($meta_file);
        $tree['all_meta'] = array_merge($meta_data,$tree['meta']);
        $tree['redundant'] = array_intersect_assoc($meta_data,$tree['meta']); //PHP 4.3.0 dependency.
	} else {
        $tree['all_meta'] = $meta_data;
	}
	
	$directory = opendir($current_path);
	
	while (($file = readdir($directory)) !== false) {
		if(substr($file,0,1) != '.') {
			$filename = $current_path.$file;
			if(is_dir($filename) && ($file != $includes_dir)) {
				$tree['dir'][$file]['subdir'] = load_sitemap($file.'/',$tree['all_meta'],$prefix.$dir);
			} else if(substr($file,-4) == '.php') {
			    $contents = file_get_contents($filename); //PHP 4.3.0 dependency.
			    if(preg_match('/include(_once)?\(\s?(\'|")[^\'"]*driver.php(\'|")\s?\)/i',$contents))
			        $tree['dir'][$file]['driver'] = true;
			    else
			        $tree['dir'][$file]['driver'] = false;
				$tree['dir'][$file]['filename'] = $file;
				$tree['dir'][$file]['url'] = substr($prefix.$dir.$file,strlen($cfg['root']));
				if(is_file($filename)) $tree['dir'][$file]['meta'] = get_meta_tags($filename);
				else $tree['dir'][$file]['meta'] = array();
				$tree['dir'][$file]['all_meta'] = array_merge($tree['all_meta'],$tree['dir'][$file]['meta']);
			    $tree['dir'][$file]['redundant'] = array_intersect_assoc($tree['all_meta'],$tree['dir'][$file]['meta']); //PHP 4.3.0 dependency.
			}
		}
	}
	return $tree;
}


//Recursive function for displaying a Templation sitemap.
function display_sitemap($site_tree,$depth=0,$class='',$current_depth=0) {
	global $cfg;
    $output = '';
    
	$found_something = false; //Useful for finding empty directories
	
	//Open table with $class and print filename and meta information.
	if($class) $class = 'class="'.$class.'" ';
	$output .= '<table '.$class.'cellspacing="0">';
	$output .= '<tr><td colspan="2"><h3>'.$site_tree['filename']."</h3>\n";
	if(isset($site_tree['meta']) && !empty($site_tree['meta'])) {
		$found_something = true;
		$output .= "<ul class=\"meta\">";
		foreach($site_tree['meta'] as $key=>$value) {
			$output .= "<li><strong>$key</strong>: $value</li>";
		}
		$output .= "</ul>\n";
	}
	$output .= "</td></tr>\n";
	
	
	//Print details about the contents of the directory.
        if (empty($site_tree['dir']) || !is_array($site_tree['dir'])) {
            $site_tree['dir'] = array();
        }
            foreach($site_tree['dir'] as $filename=>$arr) {		
		if(isset($arr['subdir'])) {
                    $cell_class = ' class="dir"';
                    $is_directory = true;
		} else if($arr['driver'] && $arr['all_meta']['parse_me'] && $arr['all_meta']['parse_me'] != 'false') {
                    $cell_class = ' class="parsed"';
                    $parsed = true;
                    $is_directory = false;
		} else {
                    $cell_class=' class="file"';
                    $parsed = false;
                    $is_directory = false;
		}
		
		if($is_directory) {
                    $subdir_output = display_sitemap($arr['subdir'],$depth-1,'subTable'.$current_depth+1,$current_depth+1);
		}
		
		if( ($is_directory && !empty($subdir_output)) ||
                    (($cfg['show_non_parsed'] && !$parsed && !$is_directory) || 
                     ($cfg['show_parsed'] && $parsed && !$is_directory)) 
                    ) {
                    if($is_directory) $linked_name = "<a href=\"{$_SERVER['PHP_SELF']}?url={$arr['subdir']['url']}\">$filename</a>";
			else $linked_name = $filename;
                    $output .= "<tr><td$cell_class>$linked_name</td><td>";
                    if(isset($arr['meta'])) {
                        $output .= "<ul class=\"meta\">";
                        foreach($arr['meta'] as $key=>$value) {
                            $output .= "<li".(isset($arr['redundant'][$key]) ? ' class="redundant"' : '')."><strong>$key</strong>: $value</li>";
                        }
                        $output .= "</ul>";
                    }
                    if($depth > 0 && isset($arr['subdir'])) {
                        $output .= $subdir_output;
                    }
                    $output .= "</td></tr>\n";
                    $found_something=true;
                    //This logic means any directory that contains a directory will not be considered
                    //empty.  A recursive test of some sort would be ideal, but also cumbersome.
		}
            }
        
	$output .= "</table>\n\n";
	
	if(!$found_something && !$cfg['show_empty_dirs']) return '';
	return $output;
}

function recursive_prefix($site_tree,$start_dir) {
    global $cfg;
    $directories = explode('/',trim($start_dir, '/'));
    
    $output = "<p>{$site_tree[filename]}</p>\n";
    $output .= "<ul class=\"meta\">";
    if(is_array($site_tree['meta'])) foreach($site_tree['meta'] as $key=>$value) {
        $output .= "<li><strong>$key</strong>: $value</li>";
    }
    $output .= "</ul>\n";
    
    if (isset($directories[1]) && isset($site_tree['dir'][$directories[0]]['subdir'])) {
            $output .= recursive_prefix($site_tree['dir'][$directories[0]]['subdir'],$directories[1]);
    }
    
    return $output;
}

function build_formvals() {
	global $tpl,$cfg;
	
	if(isset($tpl->hosts) && !empty($tpl->hosts)) {
   	$form['site'] = "<select name=\"site\">\n";
   	foreach($tpl->hosts as $site=>$array) $form['site'] .= "\t<option value=\"$site\">$site</option>\n";
   	$form['site'] .= "</select>\n\n";
   }  
	
	$form['depth'] = "<select name=\"depth\">\n";
	for($i = 0; $i < 10; $i++) $form['depth'] .= "\t<option value=\"$i\">$i</option>\n";
	$form['depth'] .= "</select>\n\n";
	
	$form['show'] = "<input type=\"checkbox\" name=\"show[]\" value=\"empty\">Empty Dirs
<input type=\"checkbox\" name=\"show[]\" value=\"parsed\">Parsed Files
<input type=\"checkbox\" name=\"show[]\" value=\"unparsed\">Unparsed Files
";
	
	$form['url'] = $cfg['url'];
	$form['site'] = select_from_menu($form['site'],$cfg['site']);
	$form['depth'] = select_from_menu($form['depth'],$cfg['depth']);
	$form['show'] = check_from_menu($form['show'],$cfg['show']);
	
	return $form;
}

function color_key() {
return '
<div id="key">
	<div class="dir">Directory</div>
	<div class="parsed">Parsed File</div>
	<div class="file">Unparsed File</div>
</div>
';
}
?>
