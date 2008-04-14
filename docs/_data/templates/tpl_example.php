<%%leading_php%%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>MySite: <%%title%%></title>
  <%%head%%>
  <link rel="stylesheet" href="/docs/examples/example.css" type="text/css" media="all" />
</head>
<body>
  
  [ <a href="?debug=<?= isset($_SESSION['templation_debug']) ? '0' : '1' ?>">DEBUG</a> | <a href="<?= str_replace('.php','.phps',$_SERVER['PHP_SELF']) ?>">SOURCE</a> ]
  
  <h1><%%title%%></h1>

  <div id="nav">
  <%%tpl_nav.php 1%%>
  </div>

  <div id="content">
  <%%body%%>
  </div>
  
  <%%tpl_debug.php 1%%>
  
  !!<%%image%%>
</body>
</html>
