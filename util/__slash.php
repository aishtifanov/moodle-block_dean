<?php // $Id: __slash.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $


/* CHECK \\\\ 
Чтобы выполнить поиск символа '\', его следует указать как '\\\\'. 
При чиной такой записи является то, что обратные слеши удаляются дважды: 
сначала синтаксическим анализатором, а потом - при выполнении сравнения с шаблоном, 
таким образом остается только один обратный слеш, который и будет обрабатываться.

SELECT distinct course FROM `moodle`.`mdl_resource`
where reference like '%\\\\%'
order by course

*/
    require_once('../../config.php');
  	require_once($CFG->libdir.'/filelib.php');
  	
	$s = optional_param('s', 0, PARAM_INT);          // Start id	 
	$e = optional_param('e', 0, PARAM_INT);          // End id	

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> __  Change slash ";
    print_header("$SITE->shortname: __  Change slash ", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    // ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
    // @set_time_limit(0);    @ob_implicit_flush(true);    @ob_end_flush();

	// execute_sql("UPDATE mdl_course SET lang = 'ru_utf8'");
	// id, course, name, type, reference, summary, alltext, popup, options, timemodified
	
	$searchtext = (string)$s;

	if ($s != 0)	{
		
		if ($e == 0)	{
			$e = $s;
		}	
		for ($i=$s; $i<=$e; $i++)	{
			if ($course = get_record('course', 'id', $i)) {
				echo $course->fullname . '<hr>';
				$resourses = get_records('resource', 'course', $course->id, '', 'id,reference');
				// print_r($resourses); echo '<hr>';
			 	foreach ($resourses as $resourse) 	{
		 			$newstr = str_replace('\\\\', '/', $resourse->reference);
		 			$newstr = str_replace('\\', '/', $newstr);
		 			set_field('resource', 'reference', $newstr, 'id', $resourse->id);
			 		
			 		/* $pos = strpos($resourse->reference, '\\\\');
			 		if ($pos !== false) {
			 			echo $resourse->reference . '<hr>';
			 		} */ 
			 	}
			}	else {
				error('Course not found.', '__slash.php');
			} 	
		}
		
	} 	
 
 	echo '<div align=center><form name="studentform" id="studentform" method="post" action="__slash.php">Course ID: &nbsp&nbsp'.
		 '<input type="text" name="s" size="10" value="' . $searchtext. '" />'.
	     '<input name="search" id="search" type="submit" value="GO" />'.
		 '</form></div>';

	print_footer();
	

?>


