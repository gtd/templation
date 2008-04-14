<?php include('Templation/driver.php'); ?>

<h2>1. Introduction</h2>

<h3>Overview</h3>

<p>
Templation is an extremely lightweight web framework oriented towards content-driven sites.  The core idea is to allow elements to cascade down through the directory structure, so that repeated elements only need exist in one place and can be customized easily in specific instances.   The ultimate goal is that each file in your document root only contain the content for that single page; the global elements will all be specified by a template and implicitly built according to where the requested page exists within the hierarchy.
</p>

<h4>Project Goals</h4>

<ol>
	<li>Eliminate redundancy of page elements by hooking into the natural hierarchy of a standard website.</li>
	<li>Work primarily with standard HTML/XHTML so it is easy to learn and work with in any HTML editor.</li>
	<li>Stay out of the way of existing code and stay as compatible as possible with other <acronym title="PHP: Hypertext Processor">PHP</acronym> applications.</li>
	<li>Provide facilities to perform arbitrary transformations of web pages.</li>
	<li>Cache and export all of the above functionality so it can be used either live or as a development tool.</li>
	<li>Easy to install and configure so that it can easily be added incrementally to any site.</li>
</ol>


<h3>Terminology</h3>

<p>Templation defines a whole new method of website construction and thus requires a slew of specific terms to be used in the documentation.  Please be familiar with the following definitions:</p>

<dl>
	<dt>Settings / Configuration</dt>
		<dd>The Templation configuration is controlled by the Templation class variables which are initialized to standard values that can be modified in the <code>settings.php</code> file.</dd>

	<dt>Data repository</dt>
		<dd>This is the directory containing templates, widgets, actions, top-level includes, and settings.  By default this will be a directory named <code>data</code> relative to the Templation class file, but it's often handy to move it to a more accessible location.</dd>

	<dt>Source file(s)</dt>
		<dd>The source file refers to the actual page requested from the web server which should ideally contain only the content specific to that page.  When used in plural, source files may refer to any file being read in for content such as include files.  Source file specifically does <em>not</em> refer to files that operate as part of Templation, this includes the Templation class, and any widgets or actions.</dd>

	<dt>Widget</dt>
		<dd>A widget is a piece of PHP code that is called by a template to generate some output.  This differs from other PHP in your source files because it is executed with Templation and its output will be cached, which means there are special implications for writing proper widgets.</dd>
		
	<dt>Action</dt>
		<dd>Actions are similar to widgets, except they are specified in meta tags rather than in templates and they run after the page is completely built, so they are able to perform transformations of complete documents.</dd>

	<dt>Hierarchy</dt>
		<dd>The Templation hierarchy is the series of file system directories between the source file and the website root directory, with a top-level available in the data repository for server-wide data.  The main purpose of Templation is to provide hooks to control the output of your pages at any of these levels or even across multiple sites.  Templation hierarchy information is stored in special subdirectories in order to keep it separate from standard files.</dd>

	<dt>Include</dt>
		<dd>One of Templation's core functions is to specify a filename that will be recursively located in the hierarchy.  This means that a template can specify an include file by name, but that file may vary by the source file location.  Widgets also an <acronym title="Application Programming Interface">API</acronym> that allows direct access to includes from the hierarchy.</dd>

	<dt>Meta data</dt>
		<dd>Meta data is the information gathered from the hierarchy that applies to a given page.  This data takes the form of key/value pairs specified by standard HTML meta tags in the source file and throughout the hierarchy.  The term 'meta data' is a bit of a misnomer because meta data is often used as literal content.  However, it is also used to control Templation itself (eg. the <code>template</code> meta tag specifies which template to use). Templation makes no distinction between control data and content because in practice you may want some control to depend on the value of data and vice-versa.</dd>
		
	<dt>Templation execution phase</dt>
		<dd>Templation is written in PHP, but it can be used to manipulate files containing PHP.  Because of this, source file PHP code is not executed inline.  Rather an entire output page is constructed and then executed at the end.  This provides many benefits including cacheable execution, easier debugging, and complete site exporting.  The Templation execution phase is the first part of execution where the majority of Templation code (and widgets) are executed.</dd>
</dl>


<h3>Philosophy</h3>

<p>
Templation started life simply named <acronym title="Content Management System">CMS</acronym>. Indeed Templation makes content easy to manage, but unlike every single content management system on the market, it does so without diluting the collective HTML and <acronym title="Cascading Style Sheets">CSS</acronym> knowledge the web design community has built up over the years.
</p>

<p>
Traditional content management systems attempt to simplify things by building a huge rickety scaffolding and/or mashing pre-fabricated components together to form a site.  Yes, non-technical staff are empowered to update the site, but at what cost?  The simple beauty of HTML files sitting in a directory structure that mirrors the URL schema is lost forever in a sea of cumbersome web-based forms and database-camouflaged content.
</p>

<p>
Templation operates on the principle that a natural website directory structure provides a suitable framework to fix 99% of the problems with plainfile web development without resorting to a completely new development methodology.  Working with raw HTML and CSS is a lot easier and more flexible than brittle content management systems that crack at the first sign of deviation from the master plan.  Templation solves problems for web designers and programmers while leaving the higher level content-development issues to be solved separately.
</p>


<h4>What Templation is not...</h4>

<p>
Templation is not a system like <a href="http://www.postnuke.com" title="An open-source block-based content management system.">Postnuke</a> which builds sites from standard modules, and controls all aspects of navigation and display through the use of complex logic. It's also not like <a href="http://www.filenet.com/" title="An expensive enterprise-level content management system.">FileNet</a> which provides complex web-based authoring tools and workflow management for diverse corporate content teams.  By default Templation has none of the high-level functionality for producing, editing, and maintaining actual page content.  Such tools are invaluable for those few websites that are truly gargantuan and require a large staff just to maintain copy.  In fact, you can easily install or build those tools on top of Templation seamlessly.
</p>

<p>
Templation is also not a templating system such as <a href="http://smarty.php.net/" title="A commonly used free PHP templating system">Smarty</a> which allows you to create complex presentation logic for well-defined data.  That type of system is optimized for presenting a data model from an application, while Templation excels at merging the various design elements that make up a web page with content whether it comes from a series of flat files or directly out of a web application.  The power of Templation does not come from a rich template language, but rather a simple and powerful way of loading data into a template.  In Smarty, for example, you spend significant time setting up a complex template only to then be forced to coerce your data into the form that the template needs.  By contrast, Templation automatically infers which data to use based on the location where the source files are stored.  Web page design and structure tends to be similar with files that are in the same directory, so inferring content this way turns out to save a ton of work.
</p>


<p>
The majority of content management and templating systems target pretty specific needs.  Templation instead focuses on broad issues that every single web designer has faced, even on websites as small 3 or 4 static pages.  Think of it as a <em>design</em> management system that assists in setting up and manipulating a website's design infrastructure.  Once you get into the rhythm of Templation, the design process is liberated so design or architecture changes late in the game won't be nearly so costly.  CSS has delivered more than any other technology in terms of liberating content from presentation, but Templation takes CSS to the next level by allowing even the presentation <em>hooks</em> in the content to be separated out.
</p>


<h3>Concept History</h3>

<p>
The problem that initially gave rise to Templation occurred during the construction of a 50 page site with a common header and footer.  The most common solution is to use a couple server side includes at the top and bottom of each page.  Unfortunately, this solution has many maintainability issues, like what if it becomes necessary to move the include files?  Editing all those files is a pain, but nothing that can't be solved with a little Perl action.  A bigger problem is if each page suddenly requires a unique title.  Then the header must be broken into two parts, or replaced with a PHP <code>header_display()</code> function that takes the title as a parameter.  Both of these approaches reduce redundancy, but add incredibly specialized code to what should be HTML plain and simple.
</p>

<p>
But what if you could write a template with an arbitrary number fields that were defined by the individual page's content?  This is the kind of functionality associated with traditional PHP templating systems such as Smarty.  Such systems offer incredibly powerful ways to define templates, but they require all your content to be in PHP variables already, so you end up with a whole new way of adding pages to your site; one that involves a proprietary syntax and writing clever PHP scripts to minimize your work.  In effect these kind of systems are simply output engines; they streamline your PHP code by removing presentational logic.  When it comes to an average website such tools are merely obfuscation.  Templation's design acknowledges that such systems are usually overkill, instead the goal was to follow in the footsteps of successful technologies like HTML itself that succeeded by <em>keeping it simple</em>. 
</p>

<p>
HTML files and directories form the basis for the vast majority of websites, so why not leverage the information already available to make template building easier?  Templation can function entirely based on HTML with extremely simple rules.  The result is a system that can solve general redundancy issues on a site quite easily, but can also be extended to generate complex content automatically.
</p>


<h3>Example</h3>

<p>
In the following example, <code>$webroot</code> refers to your website document root location, and <code>$data</code> refers to the directory where your templation data files are stored (ie. 'data repository' as described in the next section).  The files are listed in the order they get accessed so you can get some sense of Templation's flow.  Red text indicates items of special significance to templation.
</p>

<p>There is a <a href="examples/">live extended version</a> of this example which is described in <a href="examples.php">Appendix B</a>.</p>

<div class="exampleDocument">
<p class="path"><strong>(The Source Page)</strong> <em>$webroot</em>/index.php</p>
<p class="contents">&lt;?php <strong>include('Templation/driver.php');</strong> 
header('ContentType: text/html');
?&gt;
&lt;html&gt;
&lt;head&gt;
  <strong>&lt;meta name="title" content="How to Build a Templation Site" /&gt;</strong>
  &lt;link rel="stylesheet" href="example.css" type="text/css" media="all" /&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;ol&gt;
    &lt;li&gt;Creating your templates.&lt;/li&gt;
    &lt;li&gt;Specify directory meta tags.&lt;/li&gt;
    &lt;li&gt;Create source files&lt;/li&gt;
  &lt;/ol&gt;
&lt;/body&gt;
&lt;/html&gt;</p>
<p class="description">
The include at the top of the page (and it must be the first include) starts Templation, which pulls the opening PHP block and the contents of the head and body tags for use in the template.
<div style="clear: both"> </div>
</p>
</div>

<div class="exampleDocument">
<p class="path"><strong>(Directory-Wide Meta Tags)</strong> <em>$webroot</em>/includes/meta.php</p>
<p class="contents">
&lt;meta name="<strong>template</strong>" content="example.php" /&gt;
</p>
<p class="description">
Templation gathers meta tags from all the directories above the source page in addition to any found in the file itself.  In this case, the template is specified for files in this directory and all subdirectories.
</p>
</div>


<div class="exampleDocument">
<p class="path"><strong>(The Template)</strong> <em>$data</em>/templates/example.php</p>
<p class="contents"><strong>&lt;%%leading_php%%&gt;</strong>
&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"&gt;
&lt;html xmlns="http://www.w3.org/1999/xhtml"&gt;
&lt;head&gt;
  &lt;title&gt;MySite: <strong>&lt;%%title%%&gt;</strong>&lt;/title&gt;
  <strong>&lt;%%head%%&gt;</strong>
&lt;/head&gt;
&lt;body&gt;
  &lt;h1&gt;<strong>&lt;%%title%%&gt;</strong>&lt;/h1&gt;

  &lt;div id="nav"&gt;
  <strong>&lt;%%inc nav.php%%&gt;</strong>
  &lt;/div&gt;

  &lt;div id="content"&gt;
  <strong>&lt;%%body%%&gt;</strong>
  &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;</p>
<p class="description">
The template is set by the meta tag above, however it could be overridden to use a different template by placing a meta tag in the source page itself.
</p>
<div style="clear: both"> </div>
</div>

<div class="exampleDocument">
<p class="path"><strong>(The Include)</strong> <em>$webroot</em>/includes/nav.php</p>
<p class="contents">&lt;ul&gt;
  &lt;li&gt;&lt;a href="page1.php"&gt;Step one.&lt;/a&gt;&lt;/li&gt;
  &lt;li&gt;&lt;a href="page2.php"&gt;Step two.&lt;/a&gt;&lt;/li&gt;
  &lt;li&gt;&lt;a href="page3.php"&gt;Step three.&lt;/a&gt;&lt;/li&gt;
&lt;/ul&gt;</p>
<p class="description">
The template specifies an include with the name 'nav.php' which coincidentally comes from a very nearby includes directory, but it could have come from an includes directory in any ancestor directory if it didn't exist here.
</p>
<div style="clear: both"> </div>
</div>

<div class="exampleDocument">
<p class="path"><strong>(The Output Page)</strong> <em>$data</em>/cache/mySite.com/index.php</p>
<p class="contents">&lt;?php
header('ContentType: text/html'); 
?&gt;
&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"&gt;
&lt;html xmlns="http://www.w3.org/1999/xhtml"&gt;
&lt;head&gt;
  &lt;title&gt;MySite: How to Build a Templation Site&lt;/title&gt;
  &lt;meta name="title" content="How to Build a Templation Site" /&gt;
  &lt;link rel="stylesheet" href="example.css" type="text/css" media="all" /&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;h1&gt;How to Build a Templation Site&lt;/h1&gt;

  &lt;div id="nav"&gt;
  &lt;ul&gt;
    &lt;li&gt;&lt;a href="page1.php"&gt;Step one.&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="page2.php"&gt;Step two.&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="page3.php"&gt;Step three.&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
  &lt;/div&gt;

  &lt;div id="content"&gt;
  &lt;ol&gt;
    &lt;li&gt;Creating your templates.&lt;/li&gt;
    &lt;li&gt;Specify directory meta tags.&lt;/li&gt;
    &lt;li&gt;Create source files&lt;/li&gt;
  &lt;/ol&gt;
  &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;</p>
<p class="description">
Templation builds this entire page and stores it in the cache along with a list of dependencies.  The final operation is to actually <code>include()</code> this stored file so that the PHP from the source file executes.  Note that the <code>include( 'Templation/driver.php' )</code> is missing from the top, this is purposefully stripped by the Templation engine to avoid infinite recursion.   
</p>
<div style="clear: both"> </div>
</div>