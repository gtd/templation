<?php
/* TPL Docs Navigation Widget */

//Read in index file to get list of files.
$fp = fopen('index.php','r') or die("Wuh?");
$toc = fread($fp,filesize('index.php'));

preg_match_all('/<a href="([^"#]*)">(.*)<\/a>/',$toc,$matches);

for($i = 0; $i < count($matches[1]); $i++) {
    $pages[] = array('link'=>$matches[1][$i], 'title'=>$matches[2][$i]);
    if( substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/') + 1) == $matches[1][$i] ) $num = $i;
}

if(isset($num)) {
    $output = '<div class="navigation">';
    $output .= '<a href="'.$pages[$num-1]['link'].'" class="leftNav"><span>'.$pages[$num-1]['title'].'</span></a>';
    $output .= '<a href="index.php" class="centerNav"><span>Table of Contents</span></a>';
    if($num+1 < count($pages)) $output .= '<a href="'.$pages[$num+1]['link'].'" class="rightNav"><span>'.$pages[$num+1]['title'].'</span></a>';
    
    $output .= "<div style=\"clear: both\"></div></div>\n\n\n";
}
?>