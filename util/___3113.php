<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

	require_once('../../config.php');

	$strtitle = '__ Insert 3113 in all curriculum';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> " . $strtitle;
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	$currtime = time();
	
	$course = get_record('course', 'id', 3113);

	$curriculums = get_records('dean_curriculum');
	foreach ($curriculums as $curriculum)	{
		
		$rec->curriculumid = $curriculum->id;
		$rec->courseid = 3113;
		$rec->cipher = 'unknown';
		$rec->auditoriumhours = 0;
		$rec->selfinstructionhours = 0;
		$rec->term = 1;
		$rec->termpaperhours = 0;
		$rec->controltype = 'zaschet';
		$rec->name = $course->fullname;
		$rec->timemodified = $currtime;
		

		if (!record_exists_select('dean_discipline', "curriculumid = {$curriculum->id} AND courseid = 3113"))	{ 
			if (insert_record('dean_discipline', $rec))	{
				print_r($rec); echo '<hr>';
			} else {
				error(get_string('errorinaddingdisc','block_dean'), "index.php");			
			}
		}	
	}
 	
	print_footer();
?>