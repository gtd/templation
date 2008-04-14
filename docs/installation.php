<?php include('Templation/driver.php'); ?>

<h2>2. Installation</h2>

<p class="note">Please note that Templation is designed for use with the <a href="http://www.apache.org/" title="Industry standard web server software">Apache</a> web server, and the installing instructions assume that's the server you will be using.  If you have a different web server with PHP/<a href="http://pear.php.net/" title="PHP Extension and Application Repository">PEAR</a> support, Templation should still work, but it may require some tweaking.</p>


<h3>Core Installation</h3>

<p>The best way to install Templation is by moving the entire Templation directory into a directory that is in your PHP <a href="http://www.php.net/manual/en/ini.core.php#ini.include-path" title="More info about PHP include_path">include_path</a>.  This way Templation's driver script can be invoked from anywhere simply using:</p>

<p class="code">include('Templation/driver.php');</p>

<p>Of course Templation will still work with an absolute include path, but that will make it harder to move your Templation-driven site between servers, and will require you to manually configure paths for the administrative tool should you choose to install them.  Using relative paths (eg. '../Templation/driver.php') is even worse because it makes it difficult to move files up or down in the filesystem.</p>


<h3>Data Repository</h3>

<p>The data repository is the directory named (surprise!) <code>data</code>.  The data repository is where you will add your templates and top-level include files and (less frequently) actions, widgets, and settings.  For installation purposes the only thing that needs to be done here is to make sure that the <code>cache</code> directory is writeable by your web server.  On many clueful shared hosts you won't have to do a thing because the web server will run PHP as <em>your user</em>, however, if that is not the case you will likely need to make the directory world writeable with a command such as:
</p>

<p class="code">chmod 777 cache</p>

<p>It's worth noting here that the location of the data repository is only a default.  If you wish to have multiple data repositories with only a single installation of Templation, you can put them anywhere you want; you only need create alternate copies of the driver.php script which pass the pertinent data repository path into the Templation constructor.  See the comments in driver.php for details.</p>


<h3>Documentation</h3>

<p>The <code>docs</code> directory contains a full copy of the documentation you are reading now.  The docs themselves are built using Templation, so they are included as a sort of test suite to help you get Templation up and running.  Simply move or copy the docs folder into a publically viewable directory on your web server.  If you're lucky the docs will appear just as they do on the official site, if not please feel free to contact the developer for support.  Eventually your problem will appear in a yet-to-be-written troubleshooting guide.</p>


<h3>Admin Tools</h3>

<p>The administration tools are not required to use Templation (it was designed and put into production before they were even conceived).  Several tools are planned, but currently the only tool in the distribution is the Visualizer which allows you to view all the meta data for an entire site all at once.  This and all future tools are/will be available in the <code>admin</code> directory.  To get them working you will need to put the admin directory somewhere accessible from the web.  The easiest way is to simply copy the admin directory to a convenient location.  If you want to keep the admin tools together with the rest of the code, you can either symlink the admin directory, or create an alias in your Apache configuration.  For more information see the <a href="admintools.php">Admin Tools documentation</a>.</p>