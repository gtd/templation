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
* This file contains a few stand-alone functions that are useful to the administrative tools.
*
* $Id: functions.inc.php,v 1.1.1.1 2005/06/19 23:19:26 dasil003 Exp $
**/

/**
 * THE FOLLOWING TWO FUNCTIONS ARE FOR PROPAGATING CHECKBOX, RADIO, AND SELECT FORM VALUES.
 * For select menus.
 */
function select_from_menu($menu,$choices,$deselect=true) {
	if ($deselect) $menu = preg_replace('/ selected(="selected")?/','',$menu);
	if(!is_array($choices)) {
	    $menu = preg_replace('/(value="'.preg_quote($choices).'")/',"$1 selected=\"selected\"",$menu);
	} else foreach($choices as $value) {
        $menu = preg_replace('/(value="'.preg_quote($value).'")/',"$1 selected=\"selected\"",$menu);
    }
	return $menu;
}

/**
 * For radio buttons and checkboxes.
 */
function check_from_menu($menu,$choices,$deselect=true) {
   	if ($deselect) $menu = preg_replace('/ checked(="checked")?/','',$menu);
	if(!is_array($choices)) {
	    $menu = preg_replace('/(value="'.preg_quote($choices).'")/',"$1 checked=\"checked\"",$menu);
	} else foreach($choices as $value) {
        $menu = preg_replace('/(value="'.preg_quote($value).'")/',"$1 checked=\"checked\"",$menu);
    }

	return $menu;
}



/**
 * array_safe slash stripping
 */
function stripslashes_array($value) {
	if(is_string($value)) return stripslashes($value);
	else if(is_array($value)) {
		foreach($value as $key=>$val) {
			$value[$key] = stripslashes_array($val);
		}
	}
	return $value;
}

//array safe add slashes
function addslashes_array( $value ) {
	if ( is_string( $value ) ) return addslashes( $value );
	else if ( is_array( $value ) ) {
		foreach ( $value as $key => $val ) {
			$value[ $key ] = addslashes_array( $val );
		}
	}
	return $value;
}


//A pair of functions for printing output to a standard file
function buf_start() {
	ob_start();
}
function buf_end($logfile = '/var/www/html/phpdebug',$clear = false) {
    $fp = fopen($logfile, ($clear) ? 'w' : 'a');
	fwrite($fp,ob_get_contents());
	ob_end_clean();
}
function buf_debug($stuff,$logfile = '/var/www/html/phpdebug',$clear = false) {
	buf_start();
	if(is_array($stuff)) print_r($stuff);
	else print $stuff;
	buf_end($logfile,$clear);
}

//For use in tracking performance.
function utime() { 
	$t = microtime();
	$t = explode(' ', $t ); 
	return ((double)$t[0]+(double)$t[1]);  
}
?>
