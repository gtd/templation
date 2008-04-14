<?php include('Templation/driver.php'); ?>

<h2>Widgets</h2>

<p>Templation is meant to be a barebones framework for building websites, so it doesn't generate any HTML or PHP code itself.  Nevertheless, the value of the hierarchy is multiplied by writing PHP code that uses it.  Widgets give you an easy way to write PHP code that executes in the Templation execution phase and is able to make use of Templation's abilities through a clean API.  While some general-purpose widgets are available, they are most effective to simply write for yourself if you have the skills.  All widgets should be located in the <code>data/widgets</code> folder.</p>

<p>
Widgets are primarily called through tags in templates, and are executed in-line as the template is being parsed.  The widget is included in a sandbox function called <code>_processWidget()</code> which builds a Templation_Widget object once for each widget that is specified in the template.  If a widget is called more than once in a template it will use the previously instantiated object, which enables a sort of 'static' variable to be used between multiple 'calls' of the same widget within a single page.
</p>


<h3>Writing Widgets</h3>

<p>
A widget is just a chunk of code that puts its output into the variable <code>$output</code>.  The contents of that variable will be used to replace the widget tag in the template.  The meta data is available to you in an associative array named <code>$data</code> and the arguments passed in from the template will be in the associative array <code>$argv</code>.  That's all you really need to write a widget, but most useful widgets can make good use of the following API (called using <code>$this-&gt;<em>function_name</em>()</code>):
</p>

<dl>
    <dt><code>getInclude($file)</code></dt>
        <dd>Returns the contents of a file much like <code>readfile()</code>, except it adds a dependency so caching doesn't puke on you.</dd>
    <dt><code>findIncludes($file[, $num = 1[, $dir = null]])</code></dt>
        <dd>Returns an array of include pathnames found in the hierarchy starting from the source page's directory unless otherwise specified by the optional $dir parameter.</dd>
    <dt><code>getTemplate($filename)</code></dt>
        <dd>Returns the contents of a template found according to the official template rules governing template modes.  This method goes particularly nicely with the next one.</dd>
    <dt><code>parseTemplate($template, $data = null)</code></dt>
        <dd>Returns the parsed template that is passed in with optional meta data array $data.  If no $data is specified it just uses what was passed into the widget orginally.</dd>
    <dt><code>subWidget($widget, $argv = null, $data = null)</code></dt>
        <dd>Allows your widget to call another widget (or itself even!) with new arguments and meta data if necessary.</dd>
    <dt><code>setStatic($key,$val)</code></dt>
        <dd>Stores a value in the widget object which can then be retrieved by another call of the widget from the same page.</dd>
    <dt><code>getStatic($key)</code></dt>
        <dd>Retrieves a value previously set by setStatic().</dd>
    <dt><code>raiseError($msg, $code = TEMPLATION_WIDGET_WARNING)</code></dt>
        <dd>Allows you to throw an error.</dd>
</dl>

<h3>Widget Theory</h3>

<p>
Because widgets execute during the Templation execution phase, it's important to have a deep understanding of the mechanisms that Templation uses to keep it's cache up-to-date as well as the implications of the fact that the widget is not truly dynamic every time a page loads (if caching is turned on).
</p>

<p>
To avoid caching issues, make sure your widget's output depends only on the meta data, file path and possibly template mode because these things all have separate cache locations.  Never write a widget that depends on something the user passes in such as a GET variable unless you are already resigned to running with caching off (eg. tpl_debug.php widget), because the cached version will reflect what was passed in the first time the page was generated.  Code that depends on user interaction should remain as PHP code in the cache.  You can do that with a widget, but you will need to output PHP code into the $output string.  Writing code this way can be tricky and might be an indication that maybe you shouldn't be using a widget at all.
</p>

<p>
Another way to think about it is that Templation and Widgets excel at producing static webpages but don't help much with full-blown web applications.  The reason is simple: well-written web applications should be designed from the ground up to avoid the problems that Templation attempts to solve.  That doesn't mean that Templation shouldn't be mixed with web applications, it clearly is designed to allow that, but the point is to not make your web application dependent on Templation any more than necessary.
</p>

<p>
A decent litmus test of your widget is to export the site that uses it and see if the site looks and functions properly in static form.  If so then your widget is probably okay; otherwise, you may have to go back to the drawing board.  Widget-writing is a microcosm of Templation itself, there are many ways to accomplish the same thing, but experience will give you insight into the pros and cons of each one.
</p>