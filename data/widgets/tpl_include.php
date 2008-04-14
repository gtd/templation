<?php
/*
* Default Include Widget:
*
* Attempts to include files listed as parameters.  Supports both absolute paths
* and hierarchy search differentiated by the presence of leading slash.
*
* Uses no meta tags.
*/
//If we have full path then try to readfile in.
foreach ( $this->argv as $file ) {
    if ($file{0} == '/') {
        $this->getInclude($file);
    } else {
        $files = $this->findIncludes($file);
        if(empty($files)) $this->raiseError("parseTemplate(): Include file '$file' could not be found", TEMPLATION_WIDGET_WARNING);
        else if (!$fp = fopen($files[0], 'r')) {
            $this->raiseError("widget tpl_include.php: Error opening '$file' after recursive search", TEMPLATION_WIDGET_WARNING);
        } else {
            $output .= fread($fp, filesize($files[0]));
        }
    }
}


?>