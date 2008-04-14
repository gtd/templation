<?php

/* TPL Docs Anchor Adding Action */

preg_match_all('/<h3>(.*?)<\/h3>/is',$this->output,$headings);

$letter = ord('a');
for($i = 0; $i < count($headings[1]); $i++) {
    $anchor = preg_replace('/[^a-zA-Z]/','',$headings[1][$i]);
    
    $heading = $headings[1][$i];
    $replace = preg_quote('<h3>'.$heading.'</h3>','/');
    $with = '<h3>'.chr($letter).'. '.$heading.'</h3>';
    $this->output = preg_replace('/'.$replace.'/',"<a name=\"$anchor\"> </a>$with",$this->output);
    
    $letter++;   
}

?>