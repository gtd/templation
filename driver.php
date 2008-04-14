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
* To use Templation, simply include this file before any other files
* or headers are sent.
*
* $Id: driver.php,v 1.2 2005/08/02 22:52:55 dasil003 Exp $
**/

include_once('Templation.class.php');

/**
* If the templation data repository is in an alternate location, the absolute
* path should be passed to the Templation constructor.
*
* eg. $TPL = new Templation('/Users/mylogin/Sites/alt_tpl_data');
**/
$TPL = new Templation();

include( $TPL->output() );
exit;  //Explicitly exit so page contents don't get displayed twice.
?>
