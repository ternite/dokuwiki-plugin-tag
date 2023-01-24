<?php
/**
 * Options for the Tag Plugin
 */
$conf['namespace']          = 'tag';       // where should tag links point to? default: 'tag'
$conf['sortkey']            = 'title';     // sort key for topic lists
$conf['sortorder']          = 'ascending'; // ascending or descending
$conf['pagelist_flags']     = 'list';      // formatting options for the page list plugin
$conf['toolbar_icon']       = 0;	       // enables/disables the toolbar icon
$conf['list_tags_of_subns'] = 0;           // list also tags in subnamespaces of a specified namespace (count syntax)
$conf['tags_list_css']      = 'tags';      // which CSS style to use for tag list?
$conf['topic_show_output_when_empty'] = 0;      // topic mode: in case no page with the given tags could be found, should a text indicate that nothing was found?

//Setup VIM: ex: et ts=2 :
