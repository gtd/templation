<?php
/* TPL Docs Menu Building Action */

preg_match_all('/<a href="([^"#]*)">(.*)<\/a>/',$this->output,$matches);

for($i = 0; $i < count($matches[0]); $i++) {
    $fp = fopen($matches[1][$i],'r');
    $contents = fread($fp, filesize($matches[1][$i]));
    preg_match_all('/<h3>(.*?)<\/h3>/is',$contents,$headings);
    $sublinks = array();
    foreach($headings[1] as $hd) {
        $anchor = preg_replace('/[^a-zA-Z]/','',$hd);
        $sublinks[] = '<a href="'.$matches[1][$i].'#'.$anchor.'">'.$hd.'</a>';
    }
    if(!empty($sublinks)) $submenu = '<ol type="a"><li>'.implode('</li><li>',$sublinks).'</li></ol>';
    else $submenu = '';
    $this->output = preg_replace('/<a href="'.$matches[1][$i].'">(.*)<\/a>/',"$0$submenu",$this->output);
}

?>