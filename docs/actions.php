<?php include('Templation/driver.php'); ?>

<h2>8. Actions</h2>

<p>
Actions are PHP files that are executed as the last part of the Templation execution phase, after templates have been fully parsed.  Actions are much like widgets, except they don't have any strict output conventions.  An action can manipulate the Templation_File object in any way the designer likes, but the only thing that really makes sense is to modify the output of the page directly.  Actions are run from <code>Templation_File::_runActions()</code> function, and they can access the output directly using <code>this-&gt;output</code>.
</p>

<p>
Actions are specified in the meta data using the tag 'actions' and a comma-delimited list of files from the <code>data/actions</code> directory, for example:
</p>

<p class="code">
&lt;meta name="actions" content="filter.php,strip_iframes.php"&gt;
</p>

<p>
The result is that filter.php would be run first, then strip_iframes.php.  If either of those files doesn't exist in the actions directory then a warning would be raised.
</p>

<p>
Actions were implemented to serve as global filters.  The first application of actions was to convert smart quote entities that were incompatible with Netscape 4 into generic quote entities.  That was a simple one-liner, but you could use actions to perform complete transformations using a real parser.  For instance you could build your entire site as XML using Templation and put your XSLT transformations into an action.
</p>