<?php
/**
 * Tag Plugin, topic component: displays links to all wiki pages with a certain tag
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Esther Brunner <wikidesign@gmail.com>
 */

/**
 * Topic syntax, displays links to all wiki pages with a certain tag
 */
class syntax_plugin_tag_topic extends DokuWiki_Syntax_Plugin {

    /**
     * @return string Syntax type
     */
    function getType() { return 'substition'; }

    /**
     * @return string Paragraph type
     */
    function getPType() { return 'block'; }

    /**
     * @return int Sort order
     */
    function getSort() { return 295; }

    /**
     * @param string $mode Parser mode
     */
    function connectTo($mode) {
        //syntax without options catches wrong used syntax too
        $this->Lexer->addSpecialPattern('\{\{topic>}\}',$mode,'plugin_tag_topic');
        $this->Lexer->addSpecialPattern('\{\{topic>.+?\}\}',$mode,'plugin_tag_topic');
    }

    /**
     * Handle matches of the topic syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;
        $match = substr($match, 8, -2); // strip {{topic> from start and }} from end
        list($match, $flags) = array_pad(explode('&', $match, 2), 2, '');
        $flags = explode('&', $flags);
        list($ns, $tag) = array_pad(explode('?', $match), 2, '');

        if (!$tag) {
            $tag = $ns;
            $ns   = '';
        }

        if ($ns == '*' || $ns == ':') {
            $ns = '';
        } elseif ($ns == '.') {
            $ns = getNS($ID);
        } else {
            $ns = cleanID($ns);
        }

        return [$ns, trim($tag), $flags];
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $format    Renderer mode (supported modes: xhtml, odt and metadata)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler function
     * @return bool If rendering was successful.
     */
    function render($format, Doku_Renderer $renderer, $data) {
        list($ns, $tag, $flags) = $data;

        /* extract sort flags into array */
        $sortflags = [];
        foreach($flags as $flag) {
            $separator_pos = strpos($flag, '=');
            if ($separator_pos === false) {
                continue; // no "=" found, skip to next flag
            }

            $conf_name = trim(strtolower(substr($flag, 0 , $separator_pos)));
            $conf_val = trim(strtolower(substr($flag, $separator_pos+1)));

            if(in_array($conf_name, ['sortkey', 'sortorder'])) {
                $sortflags[$conf_name] = $conf_val;
            }
        }

        /* @var helper_plugin_tag $helper */
        if ($helper = $this->loadHelper('tag')) {
            $helper->overrideSortFlags($sortflags);
            $pages = $helper->getTopic($ns, '', $tag);
        }

        if (!isset($pages) || !$pages) { // nothing to display
            
            if ($this->getConf('topic_show_output_when_empty')) {
                $renderer->doc .= "<p>".$this->getLang('topic_output_when_none')."</p>";
            }
            
            return true;
        }

        if ($format == 'xhtml' || $format == 'odt') {
            /* @var Doku_Renderer_xhtml $renderer */

            // prevent caching to ensure content is always fresh
            $renderer->nocache();

            /* @var helper_plugin_pagelist $pagelist */
            // let Pagelist Plugin do the work for us
            if (!$pagelist = $this->loadHelper('pagelist')) {
                return false;
            }
            $pagelist->sort = false;
            $pagelist->rsort = false;

            $configflags = explode(',', str_replace(" ", "", $this->getConf('pagelist_flags')));
           	$flags = array_merge($configflags, $flags);
           	foreach($flags as $key => $flag) {
           		if($flag == "") {
                    unset($flags[$key]);
                }
           	}

            $pagelist->setFlags($flags);
            $pagelist->startList(null,$format);

            // Sort pages by pagename if required by flags
            if($pagelist->sort || $pagelist->rsort) {
            	$fnc = function($a, $b) {
                    return strcmp(noNS($a["id"]), noNS($b["id"]));
                };
            	usort($pages, $fnc);
            	// rsort is true - revserse sort the pages
            	if($pagelist->rsort) {
                    krsort($pages);
                }
            }

            foreach ($pages as $page) {
                $pagelist->addPage($page,$format);
            }
            $renderer->doc .= $pagelist->finishList($format);
            return true;
        }
        return false;
    }
}
