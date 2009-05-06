<?php include('Templation/driver.php'); ?>

<h2>10. Settings (Appendix A)</h2>

<p>
The Templation class lists a number of variables that can be modified globally or on a per-site basis in the settings.php file in your data repository.  Modifying the settings should not be necessary under most circumstances, so it's recommended to leave them alone unless you have moderate PHP skills and are interested in digging deeper into Templation.  Following is a brief technical description of each setting variable:
</p>

<dl>
	<dt><code>root</code></dt>
		<dd>This is the file system root of the website; it can usually be found automatically but occasionally needs to be set manually.  For instance, if your full website path contains a symlink then you may run into bugs with fopen() which render Templation inoperable.  Specifying the resolved root directory explictly can solve this type of problem.</dd>

	<dt><code>host</code></dt>
		<dd>Traditionally this is a full domain such as www.websaviour.com; however, the main purpose of this variable is to be a key into a hash of all your site configurations.  Therefore if your web server accepts connections to both www.websaviour.com and websaviour.com (and they are both the same site), the config file should make sure that accessing the site from either url sets $host to the same value.</dd>

	<dt><code>src_name</code></dt>
		<dd>The name of a server variable containing the source filename relative to the site root (ie. the portion of the URL that comes after the domain name).  By default this is set to 'SCRIPT_NAME', but under some server configurations this variable will contain the wrong file name.  For example, on Dreamhost when running PHP as CGI (the default), this variable always contains the pathname of the php.cgi executable.  In those cases you will need to specify another <code>$_SERVER</code> variable that contains the proper source file name.  If your particular server is sadistic enough to clobber all the appropriate variables and you know your way around PHP, you can always mangle your own and stick it in the <code>$_SERVER</code> hash somewhere providing the index to <code>src_name</code></dd>

	<dt><code>src_filename</code></dt>
		<dd>Similarly to <code>src_name</code>, this is <em>the name</em> of a server variable containing the full pathname to the source file.  All the same caveats apply.</dd>

	<dt><code>hosts</code></dt>
		<dd>A multi-dimensional hash of hosts.  Each top-level key corresponds to a possible value for <code>host</code>, each top-level value is a hash with custom settings for the corresponding <code>host</code>.  Any value you see listed here can be customized for an individual host, except for perhaps the <code>hosts</code> array itself which would be pretty pointless to customize.</dd>

	<dt><code>caching_on</code></dt>
		<dd>Boolean variable to turn caching on and off.  Caching minimizes processing, but sometimes needs to be turned off for debugging or advanced uses where caching would be impractical.</dd>
   
	<dt><code>includes_dir</code></dt>
		<dd>Name of directory to search for meta files and includes.</dd>

	<dt><code>meta_name</code></dt>
		<dd>Filename to parse for meta tags.</dd>
    
	<dt><code>template_mode</code></dt>
		<dd>A template with a specific name may actually be different for each template mode.  As a result each template_mode will have a separate cache.  Setting the template_mode is only meaningful if you write PHP logic that specified when you want the different versions of the same template.  See the <a href="templates.php">Templates documentation</a> for details.</dd>
	
    <dt><code>widget_statics</code></dt>
        <dd>Sometimes it's useful for widgets to have static values for internal processing purposes.  Since PHP 4 classes don't support static values, we simulate them using the Templation_Widget API which manipulates this class variable.</dd>

	<dt><code>pages</code></dt>
		<dd>Array of Templation_File objects.  By default there is only one element in this array, and it holds the constructed page.  However, Templation reserves the right to build multiple pages at once.  This functionality serves little purpose except to aid in the building of admin tools.  If you want to use this variable you should first become very familiar with Templation's main code base.</dd>

	<dt><code>index_filenames</code></dt>
		<dd>Useful information when it comes to link recognition.  Not used by the templation core, but commonly needed by widgets for such things as recognizing links to the current page etc.</dd>
</dl>
