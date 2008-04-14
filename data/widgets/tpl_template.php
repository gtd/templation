<?php
/*
* Template Widget:
*
* Includes a sub-template from your template.  The template name is passed as a parameter, 
* you can even use multiple templates to be output one after the other.
*
* Uses no meta tags.
*/
//If we have full path then try to readfile in.
foreach ( $this->argv as $file ) {
    if($file) $output .= $this->parseTemplate($this->getTemplate($file));
}
?>