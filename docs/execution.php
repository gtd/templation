<?php include('Templation/driver.php'); ?>

<h2>3. Execution</h2>

<h3>Brief</h3>

<p>
The big picture of Templation can be hard to see when scanning through the code, but the steps that it takes are all very intuitive.  Templation-based sites are often difficult to debug due to the fact that information is pulled implicitly from many locations, so a good understanding of the internals is useful.  Setting up Templation involves choices that, while seemingly trivial, can make a big impact on your workflow later on.  The first step to understanding Templation is to learn the general steps taken to build a page:

<ol type="1">
	<li>Invoke Templation driver script at the top of the requested page (a.k.a. the source page).</li>
	<li>The driver script creates a Templation instance optionally specifying the data repository containing the settings file and support files (cache, templates, widgets, etc).</li>
	<li>Templation creates a Templation_File instance which will contain all the information specific to the source page.</li>
	<li>The Templation_File gathers meta data from the hierarchy starting with from the site root and progressing all the way down to the source page.  Meta data is keyed by the 'name' attribute and can be overwritten by lower level directories or the source page itself, producing a cascading effect.  Three special values are also inserted into the meta array directly from the source page (ie. not inherited): they are the contents of the head and body tags as well as any PHP code that appears immediately at the top of the source page.  If there is no body tag, the entire source file is considered to be the body.</li>
	<li>If the meta value 'parse_me' is false for the source page then the contents of the page are used directly as output and the following 2 steps are skipped.  If 'parse_me' is true, we continue to the next step.</li>
	<li>The Templation_File uses the meta value 'template' as the filename to search for within its template directory.  It opens this file and uses it as the basis of the final output.</li>
	<li>Templation is passed the contents of the template and the meta array and parses the template by replacing simple tags with values directly from the meta_array as well as two special cases described below (includes and widgets).  Finally, the fully-parsed text is put in the output variable of the Templation_File.</li>
	<li>The output is written to a final output file so that it can be executed by include()ing it.  The final output file is located in Templation's cache directory.</li>
	<li>The output of the requested file is printed to the screen by explicitly calling the Templation_File's output() method which includes the file in-line so that any PHP it contains may be executed.</li>
	<li>The driver script must exit explicitly so that the contents of the source page are not shown again since they have already been parsed and output before the driver script is finished.</li>
</ol>

<p>
That covers the basic functionality of Templation; however, there are a few slight inaccuracies intended to make the process easier to digest.  In the following section, we break down each step into much finer technical details and explain why Templation is the way it is and point out pitfalls to avoid. 
</p>


<h3>Driver Script</h3>

<p>
The driver script is the one thing that needs to be included in every single page that uses Templation. The default driver script is located in the top level of the main Templation installation, so if you put that directory in your PHP path you can do:
</p>

<p class="code">
include('Templation/driver.php');
</p>

<p>
Using this standard include statement is always best because then you can move Templation driven sites around without changing anything in the source files.  Doing a global search and replace for this one statement might not seem like a big deal until you have to do it every time you transfer a file.  The driver script is a common place to put customizations or debugging code since it is always executed.  The default driver script only has 4 lines of code that are pretty well debugged, so customizations are unlikely to conflict with future versions.
</p>

<p>
For basic functionality the driver script does 4 simple things:
</p>

<ol type="1">
	<li>Include the Templation class file</li>
	<li>Create an instance of Templation by specifying the base directory</li>
	<li>Call the output function of the default Templation_File</li>
	<li>Exit the script (before the source page output begins)</li>
</ol>


<h3>Instantiating Templation</h3>

<p class="code">$TPL = new Templation(["<em>/absolute/path/to/Data_Repository</em>" [, "<em>/relative/path/to/page.php</em>"]]);</p>

<p>
Templation needs to know the path of the data repository.  By default this will be the data directory inside the Templation directory.  However, it is often desirable to have the data repository in an alternate location, especially if you wish to have multiple repositories.  In this case you can provide the absolute path to the Templation base directory where the settings and all associated files reside.  Additionally you can also specify what page to build initially in case you want to build something other than the page that called the driver script.  Building alternate pages is mostly useful for administrative tools that dig deep into Templation's data structures.
</p>

<p>
The Templation constructor reads in the settings.php file from the data repository, checks paths to make sure everything is in order, then instantiates a Templation_File object for the requested webpage.  The Templation_File constructor sets up a few more paths for internal use and then gathers the meta data for itself by recursing over the Templation hierarchy from the top down.  The page doesn't actually get built until the next part of the driver script.
</p>

<h3>Including the Output</h3>

<p class="code">
include( $TPL-&gt;output([<em>page_number_to_build</em>]) );
</p>

<p>
Templation tries not to do more work than it has to, and it doesn't build a page until you actually request the output.  Sometimes just looking at the meta data is enough (as is the case with the Templation Visualizer tool).  But usually you want the output, so the default driver script runs this right after instantiating Templation.  The page number to build is optional because usually you just have one page, so the output function uses the first page specified by default.
</p>

<p>
The output function checks the corresponding Templation_File if it has already been built, if not, that's when the page construction procedure is run.  The output function returns the absolute filename of the cached output.  This file may contain PHP code, so it needs to be executed by using PHP's <code>include()</code> rather than just output using PHP's <code>readfile()</code>.  Furthermore, this file contains most of the contents of the original source file, which is an important point to remember.
</p>

<h3>Exiting</h3>

<p>
So at this point we have output the final webpage.  But there's one problem, we are still running the driver script which is the first line of the actual source file.  The entire source is still to come and will be output as soon as the driver script is finished.  So to avoid outputting the contents of the original source file at the end of the Templation-constructed webpage we must exit.  This unusual order of operations is surprisingly simple in practice, although there are a few issues related to multiple PHP definitions that are described in the <a href="cache.php">Cache section</a>.
</p>
