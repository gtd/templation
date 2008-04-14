<?php include('Templation/driver.php'); ?>

<h2>6. Includes</h2>

<p>
Includes are one fundamental way of leveraging the Templation hierarchy.  Templation includes are similar regular PHP includes except your include path is dynamically extended to all the ancestor directories of the current page.  You can include the nearest file of a given name from a template using an include tag of the form <code>&lt;%%inc <em>filename</em>&gt;</code> as described on the <a href="templates.php">templates page</a>.
</p>

<h3>Hierarchy searching</h3>

<p>
Templation includes are located by first searching the 'includes' directory of every ancestor directory to the current page.  Searching progresses up to the website root directory, and then finally to the <code>data/includes</code> directory in the data repository.  The file is located from the bottom up so that the most specific file with that name is found.
</p>

<h3>API (for widgets)</h3>

<p>
Simple includes from templates are useful, but they don't begin to capture the possibilities that Templation offers.  In fact, they are really just syntactic sugar for a very simple widget (tpl_include.php).  When writing widgets, finding files in the includes hierarchy is one of the most useful functions.  The Templation_Widget includes this member function: 
</p>

<p class="code">Templation_Widget::findIncludes(<em>$filename</em>[, $number[, $directory]])</p>

<p>
Where <code>$filename</code> is the filename relative to the includes directory.  The filename may include a path if you wish, which is useful for organizing includes according to which widget uses them.  <code>$number</code> will allow you to find more than one include files going up through the hierarchy (for example see tpl_nav.php widget).
<code>$directory</code> is an optional starting directory to search in; otherwise, it just starts in the current directory the web server is loading a page from.  Remember that an 'includes' directory will be appended to the starting directory, so you should never pass in an includes directory directly, rather specify an actual content directory. <code>findInclude()</code> will return either an absolute path if one exists or false if no file is found.
</p>