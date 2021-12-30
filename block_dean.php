<?php
    require_once('lib.php');

class block_dean extends block_list
{
    function init() {
        $this->title = get_string('dean', 'block_dean');
        $this->version = 2007101010;
    }


    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';


        if (empty($this->instance)) {
            $this->content = '';
        } else {
            $this->load_content();
        }

        return $this->content;
        }

    function load_content() {
        global $CFG, $USER;
     
        $items = array();
        $icons = array();
        $index_items = get_items_menu ($items, $icons); 

		if (!empty($index_items))	{			
			foreach ($index_items as $index_item)	{
				$this->content->items[] = $items[$index_item];
				$this->content->icons[] = $icons[$index_item];
			}

   	        $this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean', 'block_dean').'</a>'.' ...';
 		}

    }


    function instance_allow_config() {
        return false;
    }


    function specialization() {
        $this->title =  get_string('dean', 'block_dean');
    }

}

?>
