<?php include('Templation/driver.php'); ?>

<h2>9. Admin Tools</h2>

<p>
Templation was built and put into production without any web-based or even command line interface at all.  The system is meant to be controlled strictly by modifying meta tags throughout your websites directories.  This arrangement is ideal for those comfortable with the command line (and indeed Templation isn't recommended for persons who are not).  Nevertheless, debugging Templation can be a mind-boggling task without some external information.  The debug widget was the first attempt to provide a website developer with some view into the internals, but it falls on its face when you want to see more than one page at once.  The gestalt view of websites is where the custom admin tools really prove their worth.  They should all be pretty easy to use, but here's a quick overview:
</p>

<h3>Visualizer</h3>

<p>
The Templation Visualizer is the main administrative tool.  It allows you to view the meta data and hierarchy in a very fine-grained manner.  You can view the hierarchy starting from any level with the ability to hide and show different categories of files.  It will even flag redundant meta tags and other anomalies.  Currently the visualizer is fairly simple; however, these significant new features are planned:
</p>

<ul>
	<li>Built-in meta tag editor.</li>
	<li>AJAX functionality to provide in-line expandable directory listings.</li>
	<li>Dependencies view.</li>
</ul>

<h3>Templation Export</h3>

<p>
Since everything that occurs during the Templation execution phase is actually cached in static files, the next logical step was to make it so that Templation can export the cache to an external web server.  The benefits are manifold:
</p>

<ul>
	<li>Static files are extremely efficient to serve.</li>
	<li>The target server need not even have PHP running.</li>
	<li>You can deliver websites in an easy-to-use package to clients while holding the magic key to quick design changes.</li>
</ul>

<p>
One of the best ways to use Export is on your local machine.  If you've got Mac OS X or Linux just get Templation set up and go.  If you're running Windows you'll need to install Apache first, but you'll be able to do local development with flexibility that Dreamweaver template users have only dreamed of.
</p>
