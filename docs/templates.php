<?php include('Templation/driver.php'); ?>

<h2>5. Templates</h2>

<p>
Every page built by templation starts with a template.  The templates are all located in the templates directory of the data repository, and are specified by the meta tag 'template'.  When a page is built, the specified template is loaded, all the tags are replaced according to the rules below, and that result is written to the cache.
</p>

<p>
Templation templates are <em>simple</em>.  Only 3 types of tags are supported and they can not be nested.  Inside a tag, parameters are separated by white space.  The three types of tags are as follows:
</p>

<dl>
	<dt><code>&lt;%%<em>key</em>%%&gt;</code> Data tags</dt>
		<dd>Injects meta data directly into the page.</dd>
	<dt><code>&lt;%%inc <em>filename(s)</em>%%&gt;</code> Include tags</dt>
		<dd>Recursive include file search throughout the hierarchy.</dd>
	<dt><code>&lt;%%<em>widget params</em>%%&gt;</code> Widget tags</dt>
		<dd>Runs a widget with the given parameters.</dd>
</dl>



<h3>Data Tags</h3>

<p>
Any tag with a single word (ie. no white space) will be replaced by the word's associated meta data.  Since meta data is specified in meta tags, it's best suited for short plaintext data, but HTML entities will be decoded in case you want to put some HTML in a meta tag.  In general it's probably better to put large sections of HTML in an include file instead.
</p>

<h3>Include Tags</h3>

<p>
A tag that begins with <code>inc</code> will be replaced by any files specified as parameters.  The files are located recursively through the hierarchy.  The contents of each file will literally replace the tag.  Any PHP code in the files will thus remain intact in the final output, thus this code stays dynamic even when templation is caching a copy of the output.  Although not originally designed this way, the <code>inc</code> tag is actually just shorthand for the tpl_include.php widget.
</p>

<h3>Widget Tags</h3>

<p>
Any tag with multiple parameters is considered a widget.  The first word of the tag is the filename of a widget located in the data repository inside the widgets folder.  Widgets are all located in that one location and have no connection to the hierarchy.  Widgets are like extensions to Templation, so it makes sense for them to be all stored in one place.
</p>

<h3>Template Modes</h3>

<p>
The template mode is a simple string ('std' by default) which can be used to provide custom versions of templates under some arbitrary conditions.  The benefit is that you can create an alternate version of your website without modifying any source files.  The limitation of template modes is that they can only provide an alternate template, but all source files, includes, and widgets that are called remain the same.  The utility of template modes depends very much on the specifics of a website setup, but in practice they can accomplish a sizeable class of design goals that would be very cumbersome using widgets and impossible using alternate CSS stylesheets.
</p>

<h4>Setting the template mode</h4>

<p>
The utility of template modes is dependent on arbitrary logic; therefore, they have to be set using PHP in your settings.php file.  The logic could be very simple, for example, if you wanted to create printer-friendly versions of all your pages you could add:
</p>

<p class="code">
if ( isset($_GET['printerfriendly']) ) $this-&gt;template_mode = 'print';
</p>

<h4>Creating alternate templates</h4>

<p>
When searching for the page template file as named in the meta data, Templation will first look in <code>data/templates/<em>template_mode</em>/</code>.  If it isn't found it will search in <code>data/templates/</code>.  So the template mode doesn't force a different version of the template unless you actually put alternate versions of templates in the respective template mode directory.  It's generally a good idea to have a standard version of all your templates in the root templates directory; otherwise you could get a "template not found" error if their is no template with a given name for certain template modes.  
</p>

<h4>Caching issues</h4>

<p>
One major consequence of providing alternate versions of templates based on arbitrary logic is that the site must be cached independently for each different template mode.  Because of this, inside the cache directory, the second level of subdirectories is based on template mode (the first level is based on host).  Even if you don't use template mode you will see the default 'std' directory there.
</p>

<p>
Having these separate caches is a boon for widget writers because widget output is cached only once each time source files are modified.  If widget output varies depending on how the page is loaded (eg. through the use of GET variables) then all the possibilities need to be cached separately.  Template modes might also be called caching modes, because they allow the web developer to set up multiple caches for arbitrary conditions.
</p>

<h4>Best practices</h4>

<p>
Template modes are an advanced feature that should only be used with good reason.  One consequence of using template modes is that your site will become difficult to export.  This is because the logic that chooses the mode will be gone, so you will need some way to glue the various versions of the site back together.
</p>

<p>
Before setting up a template mode it's worth considering alternatives.  For instance, a little PHP logic in your templates or include files may be able to solve the output issue in a more straightforward manner.  The result may be a tad slower performance-wise, but on the other hand, bandwidth is usually in shorter supply than processing capacity.  Nevertheless, template modes are a powerful tool that should be considered along with all the other options.
</p>