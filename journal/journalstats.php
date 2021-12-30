<?php // $Id: journalstats.php,v 1.3 2013/10/09 08:47:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');
    require_once('../lib_dean.php');

    $gid = required_param('gid', PARAM_INT);          // Group id
	$term = optional_param('term', 1, PARAM_INT);		// # semestra
    $fid = optional_param('fid', 0, PARAM_INT);      // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);      // Speciality id
    $cid = optional_param('cid', 0, PARAM_INT);		// Curriculum id

    $frm = data_submitted(); /// load up any submitted data
	// print_r($frm);

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string('students','block_dean');
	$strjornalgroup = get_string('journalgroup','block_dean');

	$academygroup = get_record('dean_academygroups', 'id', $gid);

	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0)  {

		$strfaculty = get_string('faculty','block_dean');
	    $strspeciality = get_string('speciality','block_dean');
		$strcurriculums = get_string('curriculums','block_dean');

	    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
		$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/academygroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
		$breadcrumbs .= " -> $strjornalgroup";

	    print_header("$site->shortname: $strjornalgroup", "$site->fullname", $breadcrumbs);

		$admin_is = isadmin();
		$creator_is = iscreator();
  	    $methodist_is = ismethodist();
//		$teacher_is = isteacherinanycourse();

	    if (!$admin_is && !$creator_is && !$methodist_is) {
	        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
	    }

		// $faculty = get_record('dean_faculty', 'id', $fid);
		// $speciality = get_record('dean_speciality', 'id', $sid);

		// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
	    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_faculty("jouirnalstats.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
	    listbox_speciality("jouirnalstats.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
	    listbox_curriculum("jouirnalstats.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
	    listbox_group_pegas("jouirnalstats.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
		echo '</table>';

	    $currenttab = 'juornalgroup';
	    include('../gruppa/tabsonegroup.php');

	    $strurl = "&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid";

	} else  {

	    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
		$breadcrumbs .= " -> $strjornalgroup";

	    print_header("$site->shortname: $strjornalgroup", "$site->fullname", $breadcrumbs);

		$admin_is = isadmin();
		$creator_is = iscreator();
		$curator_is = iscurator();
  	    $methodist_is = ismethodist();

	    if (!$curator_is && !$admin_is  && !$creator_is && !$methodist_is) {
	        error(get_string('curatoraccess', 'block_dean'), '..\faculty\faculty.php');
	    }

	    if ($curator_is) {
   			$curator = get_record('dean_curators', 'userid', $USER->id, 'academygroupid', $gid);
			$gid = 	$curator->academygroupid;
		}

		print_heading($strjornalgroup.' '.$academygroup->name, 'center', 2);

		$strurl = '';
	}

	$curriculum = get_record('dean_curriculum', 'id', $academygroup->curriculumid);
	$cid = $curriculum->id;


    $currenttabjournal ='journalstat';
	include('tabsjournal.php');

	print_tabs_semestr($term, $CFG->wwwroot."/blocks/dean/journal/journalstats.php?gid=$gid".$strurl.'&amp;term=');

	echo  '<form name="journalstats" action="journalstats.php" method="post">';
	echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
	echo  '<input type="hidden" name="term" value="'.$term.'" />';
	echo  '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
	if ($strurl != '') 	{
		echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
		echo  '<input type="hidden" name="sid" value="'.$sid.'" />';
		echo  '<input type="hidden" name="cid" value="'.$cid.'" />';
	}

	if ( !empty($frm->month_start) ) {
		$date_start	=	mktime(12, 12, 0, $frm->month_start, $frm->day_start, $frm->year_start);
		$date_end	=	mktime(12, 12, 0, $frm->month_end, $frm->day_end, $frm->year_end);
		$nowdate_1 = $date_start;
		$nowdate_2 = $date_end;
	} else {
		$startdate = get_record_sql ("SELECT * FROM {$CFG->prefix}dean_journal_startdate
									 WHERE groupid=$gid AND term=$term");
		$nowdate_2 = time();
		$nowdate_1 = date($startdate->datestart);
	}

	$strperiodwith = get_string('periodwith','block_dean'). "&nbsp&nbsp&nbsp&nbsp&nbsp";
	$strperiodon = "&nbsp&nbsp&nbsp&nbsp&nbsp". get_string('periodon','block_dean'). "&nbsp&nbsp&nbsp&nbsp&nbsp";

	echo '<div align=center>';
	print "$strperiodwith";
	print_date_selector("day_start", "month_start", "year_start", $nowdate_1);
	print "$strperiodon";
	print_date_selector("day_end", "month_end", "year_end", $nowdate_2);
	echo '<br><br>';

	echo '<input type="submit" name="stats" value="'.get_string('showstats','block_dean').'" />';
	echo '</div><br><br>';
	echo '</form>';

		if ($frm)	{
			$table->head[0] = get_string('fio', 'block_dean');
			$table->align[0] = 'left';
			for ($i=0; $i<=5; $i++)		{
				$table->head[] = get_string('p_'.$i, 'block_dean');
				$table->align[] = 'center';
			}

				$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, m.academygroupid
									FROM {$CFG->prefix}user u
							   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";
				$studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
				$studentsql .= 'ORDER BY u.lastname';

				 // print_r($studentsql);
				$students = get_records_sql($studentsql);
				if(!empty($students))   {
					foreach ($students as $student) {

						$tabledata[0] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
						for ($k=1; $k<=4;)	{
							$hours = 0;
							$strSQL = "SELECT j.id, j.userid, j.dateday, j.mark
											   FROM {$CFG->prefix}dean_journal_marks j
											   WHERE j.userid={$student->id} AND j.dateday>={$nowdate_1} AND j.dateday<={$nowdate_2} AND j.mark=$k;";
							$marks = get_records_sql($strSQL);
							if ($marks) {
								$hours = count($marks);
							}
							$k++;
							$strSQL = "SELECT j.id, j.userid, j.dateday, j.mark
											   FROM {$CFG->prefix}dean_journal_marks j
											   WHERE j.userid={$student->id} AND j.dateday>={$nowdate_1} AND j.dateday<={$nowdate_2} AND j.mark=$k;";
							$marks = get_records_sql($strSQL);
							if ($marks) {
								$hours = $hours + count($marks)*2;
							}
							$tabledata[] = $hours;
							$k++;
						}

						for ($i=5; $i<=8; $i++)		{
							$kol = 0;
							$strSQL = "SELECT j.id, j.userid, j.dateday, j.mark
											   FROM {$CFG->prefix}dean_journal_marks j
											   WHERE j.userid={$student->id} AND j.dateday>={$nowdate_1} AND j.dateday<={$nowdate_2} AND j.mark=$i;";
							$marks = get_records_sql($strSQL);
							if ($marks) {
								$kol = count($marks);
							}
							$tabledata[] = $kol;
						}
						$table->data[] = $tabledata;
						unset($tabledata);
					}
				}
			print_table($table, 1, true);
		}

    print_footer();

?>


