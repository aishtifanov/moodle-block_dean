<?php // $Id: rolls.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);		// Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
 //   $did = required_param('did', PARAM_INT);          // Discipline id
	$term = optional_param('term', 1, PARAM_INT);		// # semestra

// sdfvgdfsd
    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string('speciality','block_dean');
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string('students','block_dean');
    $strrolls = get_string("rolls","block_dean");

    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> $strgroup";

    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$teacher_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0)  {
	   $faculty = get_record_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number", true);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($sid == 0)  {
	   $speciality = get_record_sql("SELECT * FROM {$CFG->prefix}dean_speciality", true);
	}
	elseif (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
    }

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("rolls.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("rolls.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("rolls.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
    listbox_group("rolls.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode >= 4)  {

	    $currenttab = 'roll';
   	    include('../gruppa/tabsonegroup.php');


	   	print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;term=");

	    $table->head  = array ('¹', get_string('disciplinename', 'block_dean'), $SITE->teacher,
	    						get_string('date'), get_string('numberofroll','block_dean'), get_string('action','block_dean'));
	    $table->align = array ('left', 'left', 'left', 'center', 'center', 'center');

		$currcourse =  get_records_sql ("SELECT *
		 							    FROM  {$CFG->prefix}dean_discipline
									    WHERE curriculumid=$cid AND term=$term
									    ORDER BY courseid");
		$i = 0;
		if ($currcourse)
		foreach ($currcourse as $discipline) {
            $strdiscipline = $discipline->name;

			$roll =  get_record('dean_rolls', 'disciplineid', $discipline->id, 'academygroupid', $gid);

			if ($roll) 	{
 				$str1 = get_string('fill'.$discipline->controltype, 'block_dean');
 				$rid = $roll->id;
 				$teacher = get_record('user', 'id',  $roll->teacherid);
 				$teachername = fullname($teacher);
 				$printdate = get_rus_format_date($roll->datecreated, 'full');
 				$numroll = $roll->number;
			}
			else 	{
 				$str1 = get_string('create'.$discipline->controltype, 'block_dean');
 				$rid = 0;
 				$teachername = '-';
 				$printdate = '-';
 				$numroll = '-';
			}

			if 	($admin_is || $creator_is )	 {
		 		$links['edit']->url = "rollofdiscipline.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did={$discipline->id}&amp;rid=$rid&amp;term=$term";
	 			$links['edit']->title = $str1;
		 		$links['edit']->pixpath = "{$CFG->pixpath}/i/edit.gif";
   			}

	 		$links['excel']->url = "to_excel.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;did={$discipline->id}&amp;rid=$rid&amp;term=$term";
	 		$links['excel']->title = get_string('downloadexcel');
	 		$links['excel']->pixpath = "{$CFG->pixpath}/f/excel.gif";

		    $strlinkupdate = '';
		    foreach ($links as $key => $link)	{

				$strlinkupdate .= "<a title=\"$link->title\" href=\"{$link->url}\">";
				$strlinkupdate .= "<img src=\"{$link->pixpath}\" alt=\"{$link->title}\" /></a>&nbsp;";
		    }

			$i++;
			$table->data[] = array ($i, $strdiscipline, $teachername, $printdate, $numroll, $strlinkupdate);
  		}


		print_table($table);
	}

    print_footer();

?>


