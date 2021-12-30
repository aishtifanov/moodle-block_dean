<?php // $Id: index.php,v 1.0 2011/03/28 16:00:00 zagorodnyuk Exp $
    require_once("../../../config.php");
    require_once('../lib.php');

	$id = required_param('id');	     		// id category
	$sid = required_param('sid');     		// id discipline from bsu
	$cid = required_param('cid');     		// id course

	$strtitle = get_string('examschedule', 'block_dean');
	$breadcrumbs = '<a href="' . $CFG->wwwroot . '/blocks/dean/index.php">' . get_string('dean', 'block_dean') . '</a>';
	$category = get_record_select('question_categories', "id=$id", 'name');
	$title = get_string('listquestioncategory', 'block_dean');

	$breadcrumbs .= " -> <a href=\"$CFG->wwwroot/blocks/dean/quiz/index.php\">" . $strtitle . '</a>';
	$breadcrumbs .= " -> $title";

	print_header("$SITE->shortname: $title", $SITE->fullname, $breadcrumbs);

	$name = get_record_select('bsu_ref_disciplinename', "id=$sid", 'name');
    
    $strsql = "SELECT id, name
    FROM mdl_bsu_ref_disciplinename WHERE id=$id";  
    $names =  mysql_query($strsql);
    $name = mysql_fetch_assoc($names);
       
	echo '<center><table>';
	print_row(get_string('discipline', 'block_dean').':     ', $name['name']);
	$name = get_record_select('course', "id=$cid", 'fullname');
	print_row(get_string('course').':     ', $name->fullname);
	print_row(get_string('category').':     ', $category->name);
	echo '</table></center>';
	print_heading(get_string('listquestioncategory', 'block_dean'), 'center', 3);

	$questions = get_records_select('question', "category=$id AND parent=0", '', 'id, questiontext');

    $table->head  = array ('N',	get_string('question'));

    $table->align = array ("center", "left");
    $table->size = array ('5%','95%');
	$table->width = '85%';
    $table->columnwidth = array (4, 100);
    $table->titles[] = get_string('listquestion','block_dean');
	$table->downloadfilename = "listquestion";
    $table->worksheetname = 'listquestion';
	$table->titlesrows = array(20);
	$i = 1;
	foreach($questions as $data) {
		$table->data[] = array ($i++,
								$data->questiontext
								);
	}
	print_table($table);

	print_footer();
?>