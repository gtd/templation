<?php
/* TPL Docs Widget */

preg_match('/<h2[^>]*>(.*?)<\/h2>/',$data['body'],$matches);
$output = "Templation Docs - ".$matches[1];
?>