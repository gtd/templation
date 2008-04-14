<?php include('Templation/driver.php'); 
header('ContentType: text/html');
?>
<html>
<head>
  <meta name="title" content="Templation Example Homepage" />
</head>
<body>
  <p>This is a bare bones Templation site demonstrating the use of the tpl_debug and tpl_nav widgets.  See the nested navigation link to the right for a demo of the nav widgets nesting capability.</p>
  
  <p>The DEBUG link will toggle the debug widget output, and the SOURCE link will show you the syntax-colored PHP/HTML of the source file.  You can also navigate into the <a href="includes/">includes</a> directories to see the meta files and nav includes.  To see the template and widget source views are available in the <a href="source/">source</a> directory.</p>
  
  <h2>Points of Interest</h2>
  
  <ul>
    <li>Note that this page is the only source page that actually uses a pseudo-correct HTML document structure internally.  That is, this page contains HEAD and BODY elements while the other source pages just take the whole document to be implicitly the BODY.</li>
    <li>When you turn on the debug widget it will show you the meta data in hierarchical form from the top down, this goes all the way to the templation data repository at the very top where you will see 'parse_me' is on implying that all documents will be parsed unless otherwise specified somewhere lower.  The further you go down the list the more specific the meta data, and thus repeated elements will be overridden if specified multiple times.</li>
  </ul>
</body>
</html>
