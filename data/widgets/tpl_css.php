<?php
/**
* CSS Widget
*
*
* Requires Meta Tags:
* 
* - css : a comma-delimited list of css file URLs to be included.
*
* Optional Meta Tags:
* 
* - css_import : boolean of whether to import css rather than create link.
*
* Set $output at end.
*/



if(isset($data['css']) && !empty($data['css'])) {
  $output = '';
  $css = explode(',',$data['css']);
  if(isset($data['css_import']) && $data['css_import']) {
    foreach($css as $url) if(!empty($url)) $output .= '@import('.$url.')';
  } else {
    foreach($css as $url) if(!empty($url)) $output .= '<link rel="stylesheet" href="'.$url.'" type="text/css" media="all" />';
  }
}

?>
