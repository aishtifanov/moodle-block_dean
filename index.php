<?php // $Id: index.php,v 1.8 2011/04/21 08:56:31 shtifanov Exp $

    require_once('../../config.php');
    require_once('lib.php');

    require_login();

    $strdean = get_string('dean','block_dean');

    print_header("$SITE->shortname: $strdean", $SITE->fullname, $strdean);

    print_heading($strdean);

    $table->align = array ('left', 'left');

    $items = array();
    $icons = array();
    $index_items = get_items_menu ($items, $icons); 

	if (!empty($index_items))	{			
		foreach ($index_items as $index_item)	{
		    $table->data[] = array("<strong>{$items[$index_item]}</strong>" , 
                                    get_string ('description_'.$index_item, 'block_dean'));
		}
	}
    
    if (isadmin())  {
		    $table->data[] = array("<strong><a href=\"import_brc.php\">Импорт БРС</a></strong>" , "Создание ресурсов БРС"); 
    }

    print_table($table);

    print_footer();

?>


