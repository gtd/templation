<?php
/*
* Debug Widget:

* Include this widget in templates for which you wish to allow debugging.
* If the debug session variable is set, this will be output.
*
* This widget uses no meta tags or parameters.
*/
if(isset($_REQUEST['debug'])){
    if($_REQUEST['debug'] == '0' || $_REQUEST['debug'] == 'false' || $_REQUEST['debug'] == 'no') {
        unset($_SESSION['templation_debug']);
    } else {
        $_SESSION['templation_debug'] = true;
    }
} 
if(isset($_SESSION['templation_debug'])) {		
	$array_out = $this->src_page->deep_meta;
	$indx = count($this->src_page->deep_meta) - 1;
	unset($array_out[$indx]['head']);
	unset($array_out[$indx]['body']);
	unset($array_out[$indx]['leading_php']);
	
	ob_start();
	print "<div id=\"templationDebug\">\n<a href=\"?debug=0\">Exit Debug Mode</a>\n\n<pre>";
	print_r($array_out);
	print "</pre></div>";
    $output = ob_get_contents();
    ob_end_clean();
}
?>
