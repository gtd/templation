<?php
/*__________________
  NAV WIDGET
  
  This widget builds the left navigation from a series of files.  It automatically unlinks 
  the current page provided the link as the following format.
  
  <a href="/full_path">Untagged Link Name</a>
  
  Also of note, if the link is to a directory's index file, the link _MUST NOT_ explicitly include the 
  index_filename at the end of the link as it will be stripped, the trailing slash should also be 
  present (this is good practice anyway so that links appear visited).
  
  __________________
  Future Ideas
  
  Allow nest_menu to be an integer representing the depth to be nested.  Currently it's just depth 1.
  
  __________________
  Requires Meta Tags:
  
  * nav - list of navigation pieces to be found within ./includes/nav/.
    
    eg. <meta name="nav" content="menu.php,contact.php,related.php,other.php">
  
  __________________
  Optional Meta Tags (located either per-sidebar or per-document/site):
  
  * nest_menu - If true, then menu is inserted parent directory's menu (note parent sidebar must
    have the same name). Currently the regexp does not allow extra whitespace. The format for the menu 
    is a list with elements defined precisely like this:
  
    <li><a href="/full_path">Untagged Link Name</a></li>
    
    If the link is not part of a list then styling the nested menu would have to be explicit, therefore
    if the parent link is not part of a list then the result is undefined.
    
  * alt_menu - The path of an alternate menu for this menu to be nested in.  Useful if the 'parent menu'
    is in the same directory level and it's difficult to set up the names for auto-inheritance.
    
  * parent_link - If nest_menu is true and you set parent link, then the current menu will be nested under the link 
    that matches the given parent_link.  This is best accomplished by putting the parent_link meta tag inside the sidebar
    itself, although it could also be done on a page or directory basis.
  
  * dont_unlink - If true then we do not attempt to unlink and highlight current page.
  
  * special_unlink - If this is set then we will unlink this instead of the current file.
  
  __________________
  Set $output at end.
*/


if(isset($data['nav'])) $nav_list = $this->metaToArray($data['nav']);

if(!isset($nav_list)) $this->raiseError("Nav meta data not set.");  
else {
    foreach($nav_list as $nav) {
        $paths = $this->findIncludes('nav/'.$nav,2);
        if(count($paths) == 0) $this->raiseError("No '$nav' nav file found in the hierarchy.");
        else {
            $fp = fopen($paths[0], 'r');
            if(!$fp) $this->raiseError("Nav '${paths[0]}' failed to open");
            else {
                $loop_data = array_merge($data,get_meta_tags($paths[0]));
                $nav_html = fread($fp, filesize($paths[0]));
                fclose($fp);
                
                //Handle nest_menu. Very strict formatting requirements, see top.
                if(isset($loop_data['nest_menu']) && $loop_data['nest_menu'] && count($paths) > 1) {
                    $fp = fopen($paths[1], 'r');
                    if(!$fp) $this->raiseError("Parent nav '${paths[1]}' failed to open");
                    else {
                        $parent_html = fread($fp, filesize($paths[1]));
                        fclose($fp);
                    
                        //Set up default parent link.
                        $garbage_offset = strpos($paths[0], $this->src_page->tpl->includes_dir.'/nav/'.$bar);
                        $directory_of_found_nav = substr($paths[0],0,$garbage_offset);
                        $parent_link = substr($directory_of_found_nav,strlen($this->src_page->tpl->root));
                    
                        //Specified parent link.
                        if($loop_data['parent_link']) $parent_link = $loop_data['parent_link'];
                    
                        $parent_link = preg_quote($parent_link,'!');
                    
                        //insert this page's menu into parent's menu.  This regexp could be more tolerant.
                        $loop_output = preg_replace("
                            !<li>\s*(<a\s+href=\"[^\"]*$parent_link/?\")(>.*?</a>)\s*</li>!","
                            <li class=\"subhead\">\\1 class=\"listhead\"\\2\n$nav_html\n</li>\n\n",
                            $parent_html);
                    
                        //Pity there's no way to tell if preg_replace actually matched.
                        if(!strstr($loop_output,$nav_html)) $loop_output = $nav_html;
                    }
                } else { //No nest menu.
                    $loop_output = $nav_html;
                }
            
                $output .= $loop_output;
            }
        }
    }
    
    //Now remove link and embolden current link.  Strip index_filenames if any from the path.
    if(!isset($loop_data['dont_unlink']) || !$loop_data['dont_unlink']) {
        if(isset($loop_data['special_unlink']) && $loop_data['special_unlink']) {
            $file_name = $loop_data['special_unlink'];
        } else {
            $file_name = $this->src_page->rel_path;
            if( in_array( substr($file_name, strrpos($file_name, '/') + 1), $this->src_page->tpl->index_filenames) ) {
                $file_name = substr($file_name, 0, strrpos($file_name, '/') + 1);
            }
        }

        if (!empty($parent_link) && $file_name == $parent_link) {
                    $addendum = 'Subhead'; 
        } else {
                    $addendum = '';
        }
        
        $domain = preg_quote('!',$_SERVER['HTTP_HOST']);
        
        //If a class already exists we need to pull it and put it in
        if (preg_match("!
            <li([^>]*)>\s*                # M1. LI and any space
            <a
            ([^>]+?)                      # M2. At least one character (probably a space),
            (class=\"([^\"]*)\")?         # M3, M4 (class). First place a class could be.
            ([^>]*)                       # M5. If class don't exist then there might be nothing to match here, hence *.
            href=\"
            (http://(www|test)\.$domain)? # M6, M7. may or may not include the current domain (fixed for both test and www hosts)
            /?$file_name\"                #  the filename as determined above followed by more attributes if necessary
            ([^>]*)?                      # M8.
            (class=\"([^\"]*)\")?         # M9, M10 (class).  Second place a class could be.
            ([^>]*)                       # M11.
            >
            (.*?)                         # M12. the label of the link
            </a>                          # end of a
        !x",$output,$matches)) {

            if($matches[4]) $existing_classes = $matches[4].' ';
            if($matches[10]) $existing_classes = $matches[10].' ';   
        }
        
        if(!isset($existing_classes)) $existing_classes = '';


        //Same as match above.
        $output = preg_replace("!
            <li([^>]*)>\s*                # M1.
            <a
            ([^>]+?)                      # M2.
            (class=\"([^\"]*)\")?         # M3, M4 (class). First place a class could be.
            ([^>]*)                       # M5.
            href=\"
            (http://(www|test)\.$domain)? # M6, M7.
            /?$file_name\"
            ([^>]*?)                      # M8.
            (class=\"([^\"]*)\")?         # M9, M10 (class).  Second place a class could be.
            ([^>]*)                       # M11.
            >          
            (.*?)                         # M12.
            </a>                          

            !x",
            "<li\\1><a\\2\\5\\8\\11 class=\"".$existing_classes."currentPage".$addendum."\">\\12</a>",
        $output);

    }

    //Strip meta tags.
    $output = preg_replace('/\s*(<meta[^>]*>\s*)+/','',$output);
}

return $output;