<pre>
<?php 
include("Templation/Templation_Widget.class.php");

$tests = array(
  '',
  'simple,list,of,values',
  'escaped\\,comma,other',
  '\\leading slash',
  'trailing slash\\',
  ',empty,',
  'escapedslash\\\\,\\,starts with comma'
);

foreach($tests as $t) {
  print "TEST STRING <span style=\"background-color: #ccc\">$t</span>\n";
  print_r(Templation_Widget::metaToArray($t));
  print "\n\n";
}
?>
</pre>