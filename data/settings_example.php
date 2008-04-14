<?php
/**
 * Define each host that is linked to the Templation driver here.
 * Only set values defined before this point.
 */
$this->hosts = array(
	'websaviour.com' => array(
	    'root' => '/Users/mylogin/Sites/websaviour.com',
	),
	'templation.websaviour.com' => array(
		'root' => '/Users/mylogin/Sites/templation.websaviour.com'
	),
	'test.websaviour.com' => array(
		'root' => '/Users/mylogin/Sites/test.websaviour.com'
	)
);

/**
* Set Host.  $_SERVER['HTTP_HOST'] should not be used anywhere in Templation since
* a single site can often be accessed many by various hosts, particularly since
* HTTP_HOST is not generally case-sensitive.  host + rel_path defines
* a unique page.
*/
$this->host = $_SERVER['HTTP_HOST'];


//Now pick one and pull it out. and set any Templation variables it contains.
if(isset($this->hosts[$this->host]) && is_array($this->hosts[$this->host])) {
	$host_params = $this->hosts[$this->host];
	while(list($key, $val) = each($host_params))
		if(isset($host_params[$key]))
			$this->$key = $host_params[$key];
}
?>
