<?php include('Templation/driver.php'); ?>

<h2>4. Cache</h2>

<p>Templation is rather efficient at what it does, but it still has the potential to parse dozens of files to serve a single page of static content.  Templation offers a robust caching mechanism so that any given page need be constructed only once.  The cache mechanism by itself is not enough to solve all performance issues since, by its nature, Templation has to check all the files which could potentially affect the final output.  Generally these hits on the file system will not be a problem (especially with bandwidth limitations), but if your website needs to scale to truly large proportions then one final option is to use Templation as a development environment and export the entire site cache as permanent static output.</p>

<p>In order to support PHP in source files, it was necessary to sandbox Templation's operation from any code that actually needs to be dynamically run each time a page loads. This is accomplished using PHP's <code>readfile()</code> to read in source files rather than the more natural <code>include()</code>.  In this way, Templation builds up pages that actually contain PHP and writes them to their respective locations in the cache.  To finally serve the page, Templation <code>include()</code>s the cache file inside a small wrapper function.</p>

<h3>Dependencies</h3>

<p>
How does Templation know whether to rebuild a given page?  First the modification date of the cached copy is checked against the source file modification date.  That's a start, but it also must check the modification dates of all the files that affect the building of the page.  That is not a trivial task, so the first time a page is built a list of dependencies are generated and written into a file prepended with "<code>_dependencies_</code>" located next to the cached page.
</p>

<p>
Tracking dependencies becomes even more complicated when you realize that new files may be added in the hierarchy that could affect output below them.  Therefore, dependencies don't just include actual files that are found, but all the files that <em>could have been found</em>.  Thankfully Templation takes care of all this behind the scenes, but there are several circumstances where the cache can become corrupt due to dependency failure:
</p>

<ol>
	<li>The most obvious is if someone modifies or even <code>touch</code>es a file in the cache.  This will make the cache appear to be up-to-date even though it may not be.</li>
	<li>Secondly, there are certain Templation settings which can affect the building of pages, and thus render the dependencies list obsolete.  In such cases it's advisable to manually clear the cache.</li>
</ol>


<h3>Widgets</h3>

<p>
The cache is the primary reason for the existence of widgets, after all, includes can contain PHP code in them.  However, the two phases of PHP processing means that regular include files should be executed in the second stage, to avoid having the results of their first run cached.  Widgets allow the site developer to inject code into the first phase, which means that widget code is only run once for each time a source file is modified.  The implication here is that widgets are only partially dynamic because their output is cached.
</p>

<p>
Widget authors should always take great care not to access files except through the widget API which will automatically track dependencies.  So widgets should never be caught using <code>include()</code>, <code>readfile()</code>, <code>fread()</code>, or any other file manipulation functions.  The issue, however, goes deeper than that.  You also don't want to depend on the contents of a database, or the date, or anything else that can change between runs.  Such restrictions may seem draconian, but actually widgets are still widely useful, and there is room for exceptions simply by turning caching off (or clever use of <a href="templates.php#TemplateModes">template modes</a>).
</p>

<p>
See the <a href="widgets.php">widget page</a> for more information about widgets.
</p>

<h3>Cache Structure</h3>

<p>
The cache is located in an eponymous directory in the data repository.  At the top level of the cache you will find a number of directories that correspond to the setting of the <code>template_mode</code> variable.  See the <a href="templates.php">template page</a> for more information about template modes, but for now suffice it to say that each template mode requires a separate cache because the same page may use a different template.
</p>

<p>
Inside each template mode directory are directories named after the various hosts (domains) using Templation.  Within each of those directories is a precise mirror of the web structure, except only Templation-driven files exist there along with a corresponding dependencies file.
</p>

<p>
If the cache ever becomes corrupt, it's safe to delete everything inside the cache directory.  But make sure that the PHP process is able to write to the cache directory.  Under many web hosts this means going into the data repository and running something like:
</p>

<p class="code">chmod 777 cache</p>

<h3>Scope Issues</h3>

<p>
Templation attempts to operate transparently to any PHP code in the source files; however, there are some inevitable issues that come up.  The first thing is to realize that the final page is included at the end of the driver script before <code>exit()</code>ing.  The general effect is that the final output page behaves as if it were the original source file, but because PHP itself parses the entire file for function definitions before executing, it's very easy to get duplicate function definition errors.  To avoid this, function definitions need to be placed somewhere that won't be passed into the cache file.  If your source file contains a body and head elements then anything outside those will not be added to the page configuration (unless it is in the first PHP block at the top of the page).
</p>

<p>
If you have an unmodified driver script, you will also have access to the Templation object via the global <code>$TPL</code> variable, however it is not advisable to use this in source files since they will at a minimum be broken when you do a static site output, and at worse will create a self-referential nightmare of inscrutable proportions.  However, if you're into that sort of thing, the ability is there.
</p>

<h3>Static Output</h3>

<p>
One of the administrative tools (under construction) is a static site builder.  Essentially this tool crawls the site to ensure a complete cache then merges it with the source directory to create a complete site that can be uploaded as is to a static web host.  It even has facilities to convert the files (and links therein) to another extension if you wish.
</p>

